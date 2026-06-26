<?php

namespace App\Http\Middleware;

use App\Models\PersonalMedico;
use App\Services\AuditoriaService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de autenticación Firebase.
 *
 * Acepta el ID token por dos vías:
 *   1. Sesión web: el UID verificado se guardó en sesión tras el login.
 *   2. API: header Authorization: Bearer <idToken>.
 *
 * Verifica el token con kreait, resuelve el firebase_uid a un PersonalMedico
 * activo y lo deja como auth()->user(). Si el UID no corresponde a ningún
 * médico, devuelve 403 (usuario Firebase válido pero sin registro vinculado).
 */
class FirebaseAuthenticate
{
    public function __construct(
        protected FirebaseAuth $firebase,
        protected AuditoriaService $auditoria,
    ) {}

    public function handle(Request $request, Closure $next, ?string $rol = null): Response
    {
        $uid = $this->resolverUid($request);

        if (! $uid) {
            return $this->rechazar($request, 'No autenticado', 401);
        }

        $medico = PersonalMedico::where('firebase_uid', $uid)
            ->where('activo', true)
            ->first();

        if (! $medico) {
            return $this->rechazar(
                $request,
                'Tu cuenta no está vinculada a un registro de personal médico. Contacta al administrador.',
                403
            );
        }

        // Gate por rol de sistema: middleware 'firebase:admin' exige admin.
        if ($rol === 'admin' && ! $medico->esAdmin()) {
            return $this->rechazar($request, 'Requiere permisos de administrador', 403);
        }

        Auth::setUser($medico);
        $request->setUserResolver(fn() => $medico);

        return $next($request);
    }

    /**
     * Obtiene el UID verificado. En sesión web ya viene verificado del login;
     * en API verifica el Bearer token en cada request.
     */
    protected function resolverUid(Request $request): ?string
    {
        // Vía sesión (Blade)
        if ($request->hasSession() && $request->session()->has('firebase_uid')) {
            return $request->session()->get('firebase_uid');
        }

        // Vía Bearer (API)
        $token = $request->bearerToken();
        if ($token) {
            try {
                $verified = $this->firebase->verifyIdToken($token);
                return $verified->claims()->get('sub');
            } catch (FailedToVerifyToken) {
                return null;
            }
        }

        return null;
    }

    protected function rechazar(Request $request, string $mensaje, int $code): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $mensaje], $code);
        }
        return redirect()->route('login')->with('error', $mensaje);
    }
}
