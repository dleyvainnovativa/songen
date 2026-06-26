<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|------------------------------------------------------------------------------
| Bridge Firebase → personal_medico
|------------------------------------------------------------------------------
|
| Los médicos SON los usuarios del sistema. En lugar de depender de la tabla
| `users` de Laravel, autenticamos vía Firebase y resolvemos el UID a un
| registro de personal_medico.
|
|   firebase_uid  → identifica al usuario autenticado de Firebase (único)
|   rol_sistema   → nivel de acceso en la app (admin | medico)
|                   'admin' = mismo registro de médico, con permisos elevados
|   ultimo_acceso → para auditoría / panel
|
| Nota: `personal_medico` ya tiene una columna `rol` (Médico/Enfermera/etc.)
| que describe el ROL CLÍNICO. `rol_sistema` es distinto: es el ROL DE ACCESO.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personal_medico', function (Blueprint $table) {
            $table->string('firebase_uid', 128)->nullable()->unique()->after('id_medico');
            $table->enum('rol_sistema', ['admin', 'medico'])->default('medico')->after('rol');
            $table->timestamp('ultimo_acceso')->nullable()->after('activo');
        });
    }

    public function down(): void
    {
        Schema::table('personal_medico', function (Blueprint $table) {
            $table->dropColumn(['firebase_uid', 'rol_sistema', 'ultimo_acceso']);
        });
    }
};
