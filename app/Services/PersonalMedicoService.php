<?php

namespace App\Services;

use App\Models\PersonalMedico;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\EmailExists;
use Kreait\Firebase\Exception\Auth\UserNotFound;

/**
 * PersonalMedicoService — gestión del personal médico (que además son usuarios).
 *
 * Reglas de seguridad centrales (todas aquí, no en el controlador):
 *
 *   1. Borrado vs archivado: solo se BORRA si no tiene registros clínicos
 *      ligados (notas como autor, historias como responsable). Si los tiene,
 *      se ARCHIVA (activo = false) para no romper el historial.
 *
 *   2. Anti-bloqueo: un admin no puede borrarse/archivarse a sí mismo, ni
 *      quitarse el rol admin si es el último admin activo. Esto evita que el
 *      sistema quede sin ningún administrador con acceso.
 *
 *   3. Firebase opcional: al crear/editar, si se pide, se crea o enlaza la
 *      cuenta Firebase. Es tolerante a fallos: si Firebase no responde, el
 *      registro en base se guarda igual y se informa el problema.
 */
class PersonalMedicoService
{
    public function __construct(protected FirebaseAuth $firebase) {}

    /**
     * Crea un médico. Si $credenciales trae email+password, intenta crear/
     * enlazar su cuenta Firebase.
     *
     * @param array $datos        Campos del médico ya validados.
     * @param array $credenciales ['email' => , 'password' => ] opcional.
     * @return array{medico: PersonalMedico, firebase: ?string} firebase = mensaje de aviso o null
     */
    public function crear(array $datos, array $credenciales = []): array
    {
        $medico = PersonalMedico::create(array_merge($datos, [
            'activo' => $datos['activo'] ?? true,
        ]));

        $aviso = null;
        if (! empty($credenciales['email']) && ! empty($credenciales['password'])) {
            $aviso = $this->vincularFirebase($medico, $credenciales['email'], $credenciales['password']);
        }

        return ['medico' => $medico, 'firebase' => $aviso];
    }

    /**
     * Actualiza un médico. Aplica la guardia anti-bloqueo si se intenta quitar
     * el rol admin al último administrador.
     *
     * @throws ValidationException
     */
    public function actualizar(PersonalMedico $medico, array $datos): PersonalMedico
    {
        // Guardia: no permitir que el último admin se quite el rol admin.
        $quitaAdmin = isset($datos['rol_sistema'])
            && $medico->rol_sistema === 'admin'
            && $datos['rol_sistema'] !== 'admin';

        if ($quitaAdmin && $this->esUltimoAdmin($medico)) {
            throw ValidationException::withMessages([
                'rol_sistema' => 'No puedes quitar el rol de administrador: es el último administrador activo.',
            ]);
        }

        $medico->update($datos);
        return $medico->fresh();
    }

    /**
     * Elimina o archiva según tenga registros clínicos ligados.
     * Aplica guardias anti-bloqueo (no a sí mismo, no al último admin).
     *
     * @return string 'eliminado' | 'archivado'
     * @throws ValidationException
     */
    public function eliminarOArchivar(PersonalMedico $medico): string
    {
        $this->garantizarNoEsUnoMismo($medico);
        $this->garantizarNoEsUltimoAdmin($medico);

        $tieneNotas      = $medico->notasMedicas()->exists();
        $tieneHistorias  = $medico->historiasResponsable()->exists();

        if ($tieneNotas || $tieneHistorias) {
            // Tiene historial clínico → archivar, no borrar.
            $medico->update(['activo' => false]);
            return 'archivado';
        }

        $medico->delete();
        return 'eliminado';
    }

    /** Reactiva un médico archivado. */
    public function reactivar(PersonalMedico $medico): PersonalMedico
    {
        $medico->update(['activo' => true]);
        return $medico;
    }

    /* ── Guardias ────────────────────────────────────────────────────────── */

    private function garantizarNoEsUnoMismo(PersonalMedico $medico): void
    {
        if ((int) Auth::id() === (int) $medico->id_medico) {
            throw ValidationException::withMessages([
                'personal' => 'No puedes eliminarte ni archivarte a ti mismo.',
            ]);
        }
    }

    private function garantizarNoEsUltimoAdmin(PersonalMedico $medico): void
    {
        if ($medico->rol_sistema === 'admin' && $this->esUltimoAdmin($medico)) {
            throw ValidationException::withMessages([
                'personal' => 'No se puede eliminar/archivar al último administrador activo.',
            ]);
        }
    }

    /** ¿Es el único admin activo (excluyéndolo a él del conteo)? */
    private function esUltimoAdmin(PersonalMedico $medico): bool
    {
        $otrosAdmins = PersonalMedico::where('rol_sistema', 'admin')
            ->where('activo', true)
            ->where('id_medico', '!=', $medico->id_medico)
            ->count();

        return $otrosAdmins === 0;
    }

    /* ── Firebase ────────────────────────────────────────────────────────── */

    /**
     * Crea (o reutiliza) la cuenta Firebase y enlaza el uid al médico.
     * Tolerante a fallos: devuelve un mensaje de aviso si algo sale mal, o null
     * si todo bien.
     */
    public function vincularFirebase(PersonalMedico $medico, string $email, string $password): ?string
    {
        try {
            try {
                $user = $this->firebase->getUserByEmail($email);
            } catch (UserNotFound) {
                $user = $this->firebase->createUser([
                    'email'       => $email,
                    'password'    => $password,
                    'displayName' => $medico->nombre_completo,
                ]);
            }

            // ¿Ese uid ya está enlazado a otro médico?
            $ocupado = PersonalMedico::where('firebase_uid', $user->uid)
                ->where('id_medico', '!=', $medico->id_medico)
                ->exists();
            if ($ocupado) {
                return 'La cuenta Firebase ya está enlazada a otro médico; no se enlazó.';
            }

            $medico->update([
                'firebase_uid' => $user->uid,
                'email'        => $medico->email ?: $email,
            ]);

            return null; // éxito
        } catch (EmailExists) {
            return 'El correo ya existe en Firebase con otro método de acceso.';
        } catch (\Throwable $e) {
            return 'No se pudo crear/enlazar la cuenta Firebase: ' . $e->getMessage();
        }
    }
}
