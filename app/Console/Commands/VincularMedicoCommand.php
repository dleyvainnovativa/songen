<?php

namespace App\Console\Commands;

use App\Models\PersonalMedico;
use Illuminate\Console\Command;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\EmailExists;
use Kreait\Firebase\Exception\Auth\UserNotFound;

/**
 * Vincula (o crea) un usuario de Firebase a un registro de personal_medico.
 *
 * Es la vía de onboarding: como los médicos SON los usuarios, alguien tiene
 * que crear su cuenta Firebase y enlazarla. Útil para el primer admin y para
 * dar de alta médicos sin construir todavía la UI de gestión.
 *
 * Uso:
 *   php artisan fisio:vincular-medico {id_medico} {email} {--password=} {--admin} {--crear}
 *
 *   --crear     Crea el usuario en Firebase si no existe (con --password).
 *   --admin     Marca rol_sistema = admin.
 *   --password  Contraseña para el usuario nuevo (solo con --crear).
 *
 * Ejemplos:
 *   # Médico ya existe en Firebase, solo enlazar:
 *   php artisan fisio:vincular-medico 1 dra.lopez@clinica.mx
 *
 *   # Crear cuenta Firebase + enlazar como admin:
 *   php artisan fisio:vincular-medico 1 admin@clinica.mx --crear --password=Secreta123 --admin
 */
class VincularMedicoCommand extends Command
{
    protected $signature = 'fisio:vincular-medico
        {id_medico : ID del registro en personal_medico}
        {email : Correo del usuario Firebase}
        {--password= : Contraseña (solo con --crear)}
        {--admin : Asignar rol_sistema = admin}
        {--crear : Crear el usuario en Firebase si no existe}';

    protected $description = 'Vincula o crea un usuario Firebase y lo enlaza a un personal_medico';

    public function handle(FirebaseAuth $firebase): int
    {
        $idMedico = (int) $this->argument('id_medico');
        $email    = $this->argument('email');

        $medico = PersonalMedico::find($idMedico);
        if (! $medico) {
            $this->error("No existe personal_medico con id {$idMedico}.");
            return self::FAILURE;
        }

        // Resolver o crear el usuario Firebase.
        try {
            $user = $firebase->getUserByEmail($email);
            $this->info("Usuario Firebase encontrado: {$user->uid}");
        } catch (UserNotFound) {
            if (! $this->option('crear')) {
                $this->error("No existe usuario Firebase con {$email}. Usa --crear para crearlo.");
                return self::FAILURE;
            }
            $password = $this->option('password');
            if (! $password || strlen($password) < 6) {
                $this->error('Para crear el usuario necesitas --password de al menos 6 caracteres.');
                return self::FAILURE;
            }
            try {
                $user = $firebase->createUser([
                    'email'       => $email,
                    'password'    => $password,
                    'displayName' => $medico->nombre_completo,
                ]);
                $this->info("Usuario Firebase creado: {$user->uid}");
            } catch (EmailExists) {
                $this->error('El correo ya está registrado en Firebase con otro método.');
                return self::FAILURE;
            }
        }

        // Verificar que el UID no esté ya enlazado a otro médico.
        $ocupado = PersonalMedico::where('firebase_uid', $user->uid)
            ->where('id_medico', '!=', $medico->id_medico)
            ->first();
        if ($ocupado) {
            $this->error("Ese UID ya está enlazado al médico #{$ocupado->id_medico} ({$ocupado->nombre_completo}).");
            return self::FAILURE;
        }

        // Enlazar.
        $medico->firebase_uid = $user->uid;
        $medico->email = $medico->email ?: $email;
        if ($this->option('admin')) {
            $medico->rol_sistema = 'admin';
        }
        $medico->save();

        $this->info(sprintf(
            'Listo: %s ↔ %s (rol: %s)',
            $medico->nombre_completo,
            $email,
            $medico->rol_sistema
        ));

        return self::SUCCESS;
    }
}
