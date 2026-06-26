<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|------------------------------------------------------------------------------
| paciente_documentos
|------------------------------------------------------------------------------
|
| Documentos adjuntos por paciente (PDF, imágenes, Office). El archivo físico
| vive en storage/app/public/documentos/{id_paciente}/...; aquí guardamos solo
| la metadata y la ruta relativa.
|
| IMPORTANTE — tipos de FK:
| El esquema existente viene de un dump donde las PK son `int(10) unsigned`
| (NO bigint). Por eso la columna id_paciente debe ser unsignedInteger, no
| unsignedBigInteger; de lo contrario MySQL rechaza la llave foránea con
| errno 150 ("Foreign key constraint is incorrectly formed").
|
| id_medico se referencia a personal_medico (también int(10) unsigned), pero
| no se le pone FK formal para no acoplar el borrado a esa tabla; queda como
| índice lógico nullable.
|
*/

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paciente_documentos', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->bigIncrements('id_documento');

            // Coincide con pacientes.id_paciente → int(10) unsigned
            $table->unsignedInteger('id_paciente');

            // Coincide con personal_medico.id_medico → int(10) unsigned (sin FK formal)
            $table->unsignedInteger('id_medico')->nullable();

            $table->string('titulo', 150);
            $table->string('categoria', 60)->nullable();   // Estudio, Receta, Consentimiento, Otro…
            $table->string('nombre_archivo', 255);         // nombre original
            $table->string('ruta', 512);                   // ruta relativa en el disk 'public'
            $table->string('mime', 100);
            $table->unsignedBigInteger('tamano_bytes')->default(0);
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index('id_paciente');
            $table->index('id_medico');

            $table->foreign('id_paciente')
                ->references('id_paciente')->on('pacientes')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paciente_documentos');
    }
};
