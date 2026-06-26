<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('establecimientos', function (Blueprint $table) {
            $table->smallIncrements('id_establecimiento');
            $table->string('nombre', 150);
            $table->string('razon_social', 200)->nullable();
            $table->char('rfc', 13)->nullable();
            $table->string('licencia_sanitaria', 60)->nullable(); // Número COFEPRIS
            $table->string('domicilio', 255);
            $table->string('colonia', 100)->nullable();
            $table->string('municipio', 100);
            $table->string('estado', 60);
            $table->char('cp', 5)->nullable();
            $table->string('telefono', 15)->nullable();
            $table->string('email', 120)->nullable();
            $table->tinyInteger('nivel_atencion')->unsigned()->nullable(); // 1=Primario,2=Secundario,3=Terciario
            $table->date('fecha_registro')->default(now());
        });

        Schema::create('personal_medico', function (Blueprint $table) {
            $table->increments('id_medico');
            $table->unsignedSmallInteger('id_establecimiento');
            $table->string('nombre', 80);
            $table->string('primer_apellido', 60);
            $table->string('segundo_apellido', 60)->nullable();
            $table->string('cedula_profesional', 30);
            $table->string('cedula_especialidad', 30)->nullable();
            $table->unsignedTinyInteger('id_especialidad')->nullable();
            $table->enum('rol', ['Médico', 'Enfermera/o', 'Anestesiólogo', 'Residente', 'Otro']);
            $table->string('email', 120)->nullable();
            $table->string('telefono', 15)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('id_establecimiento')->references('id_establecimiento')->on('establecimientos');
            $table->foreign('id_especialidad')->references('id_especialidad')->on('cat_especialidades');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_medico');
        Schema::dropIfExists('establecimientos');
    }
};
