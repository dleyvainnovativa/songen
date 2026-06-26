<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cat_tipos_sangre', function (Blueprint $table) {
            $table->tinyIncrements('id_tipo_sangre');
            $table->string('descripcion', 5);
        });

        Schema::create('cat_estados_civiles', function (Blueprint $table) {
            $table->tinyIncrements('id_estado_civil');
            $table->string('descripcion', 30);
        });

        Schema::create('cat_escolaridades', function (Blueprint $table) {
            $table->tinyIncrements('id_escolaridad');
            $table->string('descripcion', 50);
        });

        Schema::create('cat_especialidades', function (Blueprint $table) {
            $table->tinyIncrements('id_especialidad');
            $table->string('nombre', 100);
            $table->boolean('cedula_requerida')->default(true);
        });

        Schema::create('cat_estados_cita', function (Blueprint $table) {
            $table->tinyIncrements('id_estado_cita');
            $table->string('descripcion', 30);
            // Programada | Confirmada | Cancelada | Completada | No asistió
        });

        Schema::create('cat_tipos_nota', function (Blueprint $table) {
            $table->tinyIncrements('id_tipo_nota');
            $table->string('descripcion', 60);
            // Evolución | Interconsulta | Egreso | Preoperatoria | Postoperatoria…
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cat_tipos_nota');
        Schema::dropIfExists('cat_estados_cita');
        Schema::dropIfExists('cat_especialidades');
        Schema::dropIfExists('cat_escolaridades');
        Schema::dropIfExists('cat_estados_civiles');
        Schema::dropIfExists('cat_tipos_sangre');
    }
};
