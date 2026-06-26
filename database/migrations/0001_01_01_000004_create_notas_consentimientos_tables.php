<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Consentimiento Informado (NOM-004-SSA3-2012 §8) ────────────────────
        Schema::create('consentimientos_informados', function (Blueprint $table) {
            $table->increments('id_consentimiento');
            $table->unsignedInteger('id_paciente');
            $table->unsignedInteger('id_medico');
            $table->string('tipo', 100);                           // Hospitalización | Cirugía | Anestesia…
            $table->text('descripcion_procedimiento');
            $table->text('riesgos_explicados')->nullable();
            $table->text('alternativas_explicadas')->nullable();
            $table->boolean('firma_paciente')->default(false);
            $table->string('firma_testigo1', 150)->nullable();
            $table->string('firma_testigo2', 150)->nullable();
            $table->string('representante_legal', 150)->nullable();
            $table->string('parentesco_representante', 60)->nullable();
            $table->string('motivo_representante', 200)->nullable();
            $table->dateTime('fecha_firma')->useCurrent();
            $table->dateTime('fecha_revocacion')->nullable();
            $table->string('documento_url', 512)->nullable();       // Ruta al PDF digitalizado
            $table->timestamps();

            $table->foreign('id_paciente')->references('id_paciente')->on('pacientes');
            $table->foreign('id_medico')->references('id_medico')->on('personal_medico');
        });

        // ── Notas médicas (NOM-004 §7.2 — formato SOAP) ────────────────────────
        Schema::create('notas_medicas', function (Blueprint $table) {
            $table->increments('id_nota');
            $table->unsignedInteger('id_paciente');
            $table->unsignedInteger('id_medico');
            $table->unsignedTinyInteger('id_tipo_nota');
            $table->dateTime('fecha_hora')->useCurrent();

            // SOAP
            $table->text('subjetivo')->nullable();
            $table->text('objetivo')->nullable();
            $table->text('analisis')->nullable();
            $table->text('plan')->nullable();

            // Signos vitales en la nota
            $table->string('presion_arterial', 15)->nullable();
            $table->unsignedTinyInteger('frecuencia_cardiaca')->nullable();
            $table->unsignedTinyInteger('frecuencia_respiratoria')->nullable();
            $table->decimal('temperatura_c', 4, 1)->nullable();
            $table->unsignedTinyInteger('saturacion_o2')->nullable();
            $table->decimal('peso_kg', 5, 2)->nullable();

            // Control documental (NOM-004 §5.10 — fecha, hora y firma obligatorias)
            $table->boolean('firmada')->default(false);
            $table->dateTime('fecha_firma')->nullable();
            $table->timestamps();

            $table->foreign('id_paciente')->references('id_paciente')->on('pacientes');
            $table->foreign('id_medico')->references('id_medico')->on('personal_medico');
            $table->foreign('id_tipo_nota')->references('id_tipo_nota')->on('cat_tipos_nota');
        });

        // ── Nota quirúrgica (NOM-004 §7.3) ─────────────────────────────────────
        Schema::create('notas_quirurgicas', function (Blueprint $table) {
            $table->increments('id_nota_qx');
            $table->unsignedInteger('id_nota')->unique();
            $table->unsignedInteger('id_paciente');
            $table->unsignedInteger('id_cirujano');
            $table->unsignedInteger('id_anestesiologo')->nullable();
            $table->dateTime('fecha_hora_inicio');
            $table->dateTime('fecha_hora_fin')->nullable();
            $table->string('tipo_anestesia', 60)->nullable();
            $table->text('diagnostico_preoperatorio');
            $table->text('diagnostico_postoperatorio')->nullable();
            $table->text('procedimiento_realizado');
            $table->text('hallazgos')->nullable();
            $table->text('tecnica')->nullable();
            $table->string('material_implantado', 200)->nullable();
            $table->text('complicaciones')->nullable();
            $table->unsignedSmallInteger('sangrado_ml')->nullable();
            $table->unsignedSmallInteger('diuresis_ml')->nullable();
            $table->enum('estado_egreso', ['Estable', 'Inestable', 'Crítico'])->nullable();
            $table->timestamps();

            $table->foreign('id_nota')->references('id_nota')->on('notas_medicas');
            $table->foreign('id_paciente')->references('id_paciente')->on('pacientes');
            $table->foreign('id_cirujano')->references('id_medico')->on('personal_medico');
            $table->foreign('id_anestesiologo')->references('id_medico')->on('personal_medico');
        });

        // ── Medicamentos catálogo ───────────────────────────────────────────────
        Schema::create('medicamentos', function (Blueprint $table) {
            $table->increments('id_medicamento');
            $table->string('nombre_generico', 150);
            $table->string('nombre_comercial', 150)->nullable();
            $table->string('forma_farmaceutica', 60)->nullable();
            $table->string('concentracion', 40)->nullable();
            $table->string('via_administracion', 40)->nullable();
            $table->boolean('activo')->default(true);
        });

        // ── Prescripción de medicamentos por paciente ───────────────────────────
        Schema::create('paciente_medicamentos', function (Blueprint $table) {
            $table->increments('id_pm');
            $table->unsignedInteger('id_paciente');
            $table->unsignedInteger('id_medicamento');
            $table->string('dosis', 60);
            $table->string('frecuencia', 60);
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->unsignedInteger('prescrito_por')->nullable();
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('id_paciente')->references('id_paciente')->on('pacientes');
            $table->foreign('id_medicamento')->references('id_medicamento')->on('medicamentos');
            $table->foreign('prescrito_por')->references('id_medico')->on('personal_medico');
        });

        // ── Estudios de laboratorio e imagen ───────────────────────────────────
        Schema::create('estudios', function (Blueprint $table) {
            $table->increments('id_estudio');
            $table->unsignedInteger('id_paciente');
            $table->unsignedInteger('id_medico_solicita');
            $table->enum('tipo', ['Laboratorio', 'Imagen', 'Electrodiagnóstico', 'Otro']);
            $table->string('nombre', 150);
            $table->date('fecha_solicitud');
            $table->date('fecha_resultado')->nullable();
            $table->text('resultado')->nullable();
            $table->text('interpretacion')->nullable();
            $table->string('archivo_url', 512)->nullable();  // PDF, DICOM, etc.
            $table->timestamps();

            $table->foreign('id_paciente')->references('id_paciente')->on('pacientes');
            $table->foreign('id_medico_solicita')->references('id_medico')->on('personal_medico');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estudios');
        Schema::dropIfExists('paciente_medicamentos');
        Schema::dropIfExists('medicamentos');
        Schema::dropIfExists('notas_quirurgicas');
        Schema::dropIfExists('notas_medicas');
        Schema::dropIfExists('consentimientos_informados');
    }
};
