<?php

namespace Database\Seeders;

use App\Models\PersonalMedico;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * AdminSeeder — establecimiento base + usuario administrador.
 *
 * Crea (idempotente):
 *   - Establecimiento id 1 (la clínica).
 *   - personal_medico admin (admin@songen.com), rol_sistema = admin,
 *     ligado al establecimiento 1.
 *
 * Sobre la contraseña: la auth es por Firebase, NO hay columna password en la
 * base. Por eso este seeder, además de crear las filas, INTENTA crear y enlazar
 * el usuario en Firebase (correo admin@songen.com / contraseña "temporal").
 *
 *   - Si el SDK de Firebase está configurado y accesible → crea el usuario,
 *     enlaza firebase_uid y el login queda listo.
 *   - Si NO está accesible (sin credenciales / sin red) → deja las filas en la
 *     base e indica cómo terminar con el comando artisan.
 *
 * Recomendación: cambia la contraseña "temporal" tras el primer acceso.
 */
class AdminSeeder extends Seeder
{
    private const EMAIL    = 'admin@songen.com';
    private const PASSWORD = 'temporal';

    public function run(): void
    {
        // 1. Establecimiento base (id 1).
        DB::table('establecimientos')->updateOrInsert(
            ['id_establecimiento' => 1],
            [
                'nombre'         => 'Songen',
                'razon_social'   => 'Songen',
                'domicilio'      => 'Por definir',
                'municipio'      => 'Por definir',
                'estado'         => 'Veracruz',
                'email'          => self::EMAIL,
                'fecha_registro' => now()->toDateString(),
            ]
        );

        // 2. Médico administrador (id 1), ligado al establecimiento 1.
        DB::table('personal_medico')->updateOrInsert(
            ['email' => self::EMAIL],
            [
                'id_establecimiento' => 1,
                'nombre'             => 'Sebastián',
                'primer_apellido'    => 'Guillen',
                'segundo_apellido'   => "Ruiz",
                'cedula_profesional' => 'ADMIN-0001', // NOT NULL en el esquema
                'rol'                => 'Médico',
                'rol_sistema'        => 'admin',
                'activo'             => 1,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]
        );

        $medico = PersonalMedico::where('email', self::EMAIL)->first();

        $this->command->info("Establecimiento 1 y admin ({$medico->email}) listos en la base.");

        // 3. Intentar crear/enlazar el usuario en Firebase.
        $this->vincularFirebase($medico);
    }

    /**
     * Crea (o reutiliza) el usuario Firebase y enlaza su uid al médico.
     * Tolerante a fallos: si Firebase no está disponible, informa y sigue.
     */
    private function vincularFirebase(PersonalMedico $medico): void
    {
        // Si ya está enlazado, no hacemos nada.
        if (! empty($medico->firebase_uid)) {
            $this->command->info('El admin ya tiene firebase_uid enlazado. Nada que hacer.');
            return;
        }

        try {
            /** @var \Kreait\Firebase\Contract\Auth $auth */
            $auth = app(\Kreait\Firebase\Contract\Auth::class);

            // ¿Ya existe el usuario en Firebase? Reusarlo; si no, crearlo.
            try {
                $user = $auth->getUserByEmail(self::EMAIL);
            } catch (\Kreait\Firebase\Exception\Auth\UserNotFound) {
                $user = $auth->createUser([
                    'email'       => self::EMAIL,
                    'password'    => self::PASSWORD,
                    'displayName' => $medico->nombre_completo,
                ]);
            }

            $medico->firebase_uid = $user->uid;
            $medico->save();

            $this->command->info('✅ Usuario Firebase creado/enlazado.');
            $this->command->warn("   Login: " . self::EMAIL . " / " . self::PASSWORD . " (cámbiala tras el primer acceso).");
        } catch (\Throwable $e) {
            // Firebase no disponible: dejar instrucciones claras.
            $this->command->warn('⚠ No se pudo crear/enlazar el usuario en Firebase automáticamente.');
            $this->command->warn('   Motivo: ' . $e->getMessage());
            $this->command->line('   Termina el enlace con:');
            $this->command->line('   php artisan fisio:vincular-medico ' . $medico->id_medico . ' ' . self::EMAIL . ' --crear --password=' . self::PASSWORD . ' --admin');
        }
    }
}
