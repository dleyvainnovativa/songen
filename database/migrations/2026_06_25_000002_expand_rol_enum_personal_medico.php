<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
|------------------------------------------------------------------------------
| Amplía el enum de personal_medico.rol
|------------------------------------------------------------------------------
|
| El enum original tenía: Médico, Enfermera/o, Anestesiólogo, Residente, Otro.
| Lo ampliamos a la lista de config/roles_clinicos.php (añade Fisioterapeuta,
| Recepcionista, Administrativo).
|
| Se hace con SQL crudo (ALTER ... MODIFY) porque Doctrine/Laravel no maneja
| bien el cambio de enums. La lista viene de config para no duplicar la verdad.
|
| El default se fija en 'Médico'. NOT NULL se conserva.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        $roles = config('roles_clinicos');
        $lista = collect($roles)
            ->map(fn ($r) => "'" . str_replace("'", "''", $r) . "'")
            ->implode(',');

        DB::statement("ALTER TABLE `personal_medico`
            MODIFY `rol` ENUM($lista) NOT NULL DEFAULT 'Médico'");
    }

    public function down(): void
    {
        // Vuelve al enum original de 5 valores.
        DB::statement("ALTER TABLE `personal_medico`
            MODIFY `rol` ENUM('Médico','Enfermera/o','Anestesiólogo','Residente','Otro') NOT NULL DEFAULT 'Médico'");
    }
};
