<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pacientes', function (Blueprint $table) {
            $table->increments('id_paciente');
            $table->unsignedSmallInteger('id_establecimiento');

            // Identificación (NOM-004-SSA3-2012 §5.1)
            $table->string('numero_expediente', 30)->unique();
            $table->string('nombre', 80);
            $table->string('primer_apellido', 60);
            $table->string('segundo_apellido', 60)->nullable();
            $table->date('fecha_nacimiento');
            $table->enum('sexo', ['M', 'F', 'Indeterminado']);
            $table->char('curp', 18)->unique()->nullable();
            $table->unsignedTinyInteger('id_estado_civil')->nullable();
            $table->unsignedTinyInteger('id_escolaridad')->nullable();
            $table->string('ocupacion', 80)->nullable();
            $table->string('religion', 60)->nullable();
            $table->string('etnia', 60)->nullable();

            // Domicilio
            $table->string('domicilio', 255)->nullable();
            $table->string('colonia', 100)->nullable();
            $table->string('municipio', 100)->nullable();
            $table->string('estado', 60)->nullable();
            $table->char('cp', 5)->nullable();
            $table->string('telefono', 15)->nullable();
            $table->string('email', 120)->nullable();

            // Clasificación del paciente (tabla única, herencia por extensión)
            $table->enum('tipo_paciente', ['Quirúrgico', 'Neurológico', 'Geriátrico']);

            // Datos clínicos generales
            $table->unsignedTinyInteger('id_tipo_sangre')->nullable();
            $table->text('alergias_conocidas')->nullable();

            // Archivado lógico (NOM-004 §10 — conservación mínima 5 años, no borrado físico)
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('id_establecimiento')->references('id_establecimiento')->on('establecimientos');
            $table->foreign('id_estado_civil')->references('id_estado_civil')->on('cat_estados_civiles');
            $table->foreign('id_escolaridad')->references('id_escolaridad')->on('cat_escolaridades');
            $table->foreign('id_tipo_sangre')->references('id_tipo_sangre')->on('cat_tipos_sangre');
        });

        Schema::create('paciente_contactos_emergencia', function (Blueprint $table) {
            $table->increments('id_contacto');
            $table->unsignedInteger('id_paciente');
            $table->string('nombre_completo', 150);
            $table->string('parentesco', 40)->nullable();
            $table->string('telefono', 15);
            $table->string('telefono_alt', 15)->nullable();

            $table->foreign('id_paciente')->references('id_paciente')->on('pacientes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paciente_contactos_emergencia');
        Schema::dropIfExists('pacientes');
    }
};
