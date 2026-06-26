<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PersonalMedico;
use App\Services\AuditoriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;

/**
 * Puente Firebase → sesión Laravel.
 *
 * Flujo:
 *   1. El cliente (firebase.js) hace login con email/password y obtiene un ID token.
 *   2. Lo envía a POST /auth/session.
 *   3. Aquí verificamos el token con kreait, confirmamos que el UID corresponde
 *      a un PersonalMedico activo, y guardamos firebase_uid en la sesión.
 *   4. A partir de ahí, FirebaseAuthenticate resuelve la sesión en cada request web.
 *
 * No guardamos el ID token en sesión (expira en 1h); guardamos el UID, que es
 * estable. El middleware re-resuelve el médico desde el UID en cada request.
 */
class FirebaseAuthController extends Controller
{
    public function __construct(
        protected FirebaseAuth $firebase,
        protected AuditoriaService $auditoria,
    ) {}

    /**
     * Intercambia un ID token de Firebase por una sesión Laravel.
     * POST /auth/session  { id_token }
     */
    public function session(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_token' => ['required', 'string'],
        ]);

        // 1. Verificar el token con Firebase.
        try {
            $verified = $this->firebase->verifyIdToken($data['id_token']);
        } catch (FailedToVerifyToken) {
            return response()->json([
                'message' => 'Token inválido o expirado. Intenta iniciar sesión de nuevo.',
            ], 401);
        }

        $uid = $verified->claims()->get('sub');

        // 2. Confirmar que el UID corresponde a un médico activo.
        $medico = PersonalMedico::where('firebase_uid', $uid)
            ->where('activo', true)
            ->first();

        if (! $medico) {
            return response()->json([
                'message' => 'Tu cuenta no está vinculada a un registro de personal médico. Contacta al administrador.',
            ], 403);
        }

        // 3. Establecer la sesión.
        $request->session()->regenerate();
        $request->session()->put('firebase_uid', $uid);

        // 4. Registrar acceso y sello de tiempo.
        $medico->forceFill(['ultimo_acceso' => now()])->save();
        Auth::setUser($medico);
        $this->auditoria->registrar(
            accion: AuditoriaService::CONSULTA,
            tabla: 'personal_medico',
            idRegistro: $medico->id_medico,
            descripcion: 'Inicio de sesión',
        );

        return response()->json([
            'message'  => 'Sesión iniciada',
            'redirect' => route('dashboard'),
            'medico'   => [
                'nombre' => $medico->nombre_completo,
                'rol'    => $medico->rol_sistema,
            ],
        ]);
    }

    /**
     * Cierra la sesión Laravel. El signOut de Firebase ocurre en el cliente.
     * POST /logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->session()->forget('firebase_uid');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Sesión cerrada']);
    }
}
