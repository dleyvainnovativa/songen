<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Historia clínica base (NOM-004-SSA3-2012 §7.1) ──────────────────────
        Schema::create('historias_clinicas', function (Blueprint $table) {
            $table->increments('id_historia');
            $table->unsignedInteger('id_paciente')->unique(); // 1:1 con paciente
            $table->unsignedInteger('id_medico_responsable');
            $table->dateTime('fecha_elaboracion')->useCurrent();

            // Antecedentes Heredofamiliares
            $table->boolean('ant_hf_diabetes')->default(false);
            $table->boolean('ant_hf_hipertension')->default(false);
            $table->boolean('ant_hf_cardiopatia')->default(false);
            $table->boolean('ant_hf_cancer')->default(false);
            $table->text('ant_hf_otros')->nullable();

            // Antecedentes Personales No Patológicos
            $table->boolean('tabaquismo')->default(false);
            $table->string('tabaquismo_detalle', 100)->nullable();
            $table->boolean('alcoholismo')->default(false);
            $table->string('alcoholismo_detalle', 100)->nullable();
            $table->boolean('toxicomanias')->default(false);
            $table->string('toxicomanias_detalle', 100)->nullable();
            $table->string('actividad_fisica', 100)->nullable();
            $table->string('dieta', 100)->nullable();

            // Antecedentes Personales Patológicos
            $table->text('enfermedades_previas')->nullable();
            $table->text('hospitalizaciones_previas')->nullable();
            $table->text('cirugias_previas')->nullable();
            $table->text('traumatismos_previos')->nullable();
            $table->boolean('transfusiones')->default(false);
            $table->string('transfusiones_detalle', 200)->nullable();

            // Antecedentes Ginecoobstétricos
            $table->string('menarca', 20)->nullable();
            $table->string('ciclos_menstruales', 60)->nullable();
            $table->unsignedTinyInteger('gestas')->nullable();
            $table->unsignedTinyInteger('partos')->nullable();
            $table->unsignedTinyInteger('cesareas')->nullable();
            $table->unsignedTinyInteger('abortos')->nullable();
            $table->date('fecha_ultima_regla')->nullable();

            // Padecimiento actual (obligatorio NOM-004)
            $table->text('motivo_consulta');
            $table->text('padecimiento_actual');

            // Exploración física inicial
            $table->decimal('peso_kg', 5, 2)->nullable();
            $table->decimal('talla_cm', 5, 1)->nullable();
            $table->decimal('imc', 4, 2)->nullable();  // calculado en PHP/Observer
            $table->string('presion_arterial', 15)->nullable();
            $table->unsignedTinyInteger('frecuencia_cardiaca')->nullable();
            $table->unsignedTinyInteger('frecuencia_respiratoria')->nullable();
            $table->decimal('temperatura_c', 4, 1)->nullable();
            $table->unsignedTinyInteger('saturacion_o2')->nullable();
            $table->text('exploracion_fisica')->nullable();

            // Diagnóstico y plan (obligatorio NOM-004)
            $table->text('diagnostico_inicial');
            $table->text('plan_manejo');
            $table->text('pronostico')->nullable();

            $table->timestamps();

            $table->foreign('id_paciente')->references('id_paciente')->on('pacientes');
            $table->foreign('id_medico_responsable')->references('id_medico')->on('personal_medico');
        });

        // ── Extensión Quirúrgica ────────────────────────────────────────────────
        Schema::create('hc_quirurgicas', function (Blueprint $table) {
            $table->increments('id_hc_quirurgica');
            $table->unsignedInteger('id_historia')->unique();

            $table->unsignedTinyInteger('clasificacion_asa')->nullable();     // ASA I-VI
            $table->enum('riesgo_quirurgico', ['Bajo', 'Moderado', 'Alto', 'Muy alto'])->nullable();
            $table->string('tipo_cirugia', 100)->nullable();                  // Electiva | Urgencia | Programada
            $table->text('procedimiento_previsto')->nullable();
            $table->string('anestesia_tipo', 80)->nullable();
            $table->unsignedTinyInteger('ayuno_horas')->nullable();
            $table->boolean('profilaxis_antibiotica')->default(false);
            $table->string('profilaxis_detalle', 200)->nullable();
            $table->boolean('alergias_latex')->default(false);
            $table->boolean('coagulopatia')->default(false);
            $table->string('coagulopatia_detalle', 200)->nullable();
            $table->text('observaciones')->nullable();

            $table->timestamps();
            $table->foreign('id_historia')->references('id_historia')->on('historias_clinicas');
        });

        // ── Extensión Neurológica ──────────────────────────────────────────────
        Schema::create('hc_neurologicas', function (Blueprint $table) {
            $table->increments('id_hc_neurologica');
            $table->unsignedInteger('id_historia')->unique();

            $table->unsignedTinyInteger('escala_glasgow')->nullable();        // 3–15
            $table->string('estado_mental', 100)->nullable();
            $table->string('lenguaje', 100)->nullable();
            $table->string('marcha', 100)->nullable();
            $table->string('reflejos', 200)->nullable();
            $table->text('pares_craneales')->nullable();
            $table->string('fuerza_muscular', 200)->nullable();
            $table->string('sensibilidad', 200)->nullable();
            $table->string('coordinacion', 200)->nullable();
            $table->boolean('epilepsia')->default(false);
            $table->string('epilepsia_detalle', 200)->nullable();
            $table->boolean('deterioro_cognitivo')->default(false);
            $table->string('deterioro_escala', 80)->nullable();               // MMSE | MoCA | CDR
            $table->unsignedTinyInteger('deterioro_puntaje')->nullable();
            $table->boolean('cefalea_cronica')->default(false);
            $table->boolean('ictus_previo')->default(false);
            $table->string('ictus_detalle', 200)->nullable();
            $table->text('observaciones')->nullable();

            $table->timestamps();
            $table->foreign('id_historia')->references('id_historia')->on('historias_clinicas');
        });

        // ── Extensión Geriátrica ───────────────────────────────────────────────
        Schema::create('hc_geriatricas', function (Blueprint $table) {
            $table->increments('id_hc_geriatrica');
            $table->unsignedInteger('id_historia')->unique();

            // Valoración Geriátrica Integral
            $table->unsignedSmallInteger('escala_barthel')->nullable();       // 0–100
            $table->unsignedTinyInteger('escala_lawton')->nullable();         // 0–8
            $table->unsignedTinyInteger('escala_tinetti_marcha')->nullable();
            $table->unsignedTinyInteger('escala_tinetti_equilibrio')->nullable();
            $table->enum('riesgo_caidas', ['Bajo', 'Moderado', 'Alto'])->nullable();
            $table->unsignedTinyInteger('mini_mental_mmse')->nullable();      // 0–30
            $table->unsignedTinyInteger('escala_depresion_geriatrica')->nullable(); // Yesavage
            $table->string('estado_nutricional', 80)->nullable();
            $table->unsignedTinyInteger('puntaje_nutricional')->nullable();

            // Polifarmacia (calculado en PHP)
            $table->unsignedTinyInteger('numero_medicamentos')->nullable();
            $table->boolean('polifarmacia')->default(false);                  // ≥5 medicamentos

            // Contexto social
            $table->text('red_apoyo_social')->nullable();
            $table->string('cuidador_primario', 150)->nullable();
            $table->boolean('vive_solo')->nullable();
            $table->string('tipo_vivienda', 80)->nullable();

            // Síndromes geriátricos
            $table->boolean('sindrome_fragilidad')->default(false);
            $table->boolean('sarcopenia')->default(false);
            $table->boolean('incontinencia_urinaria')->default(false);
            $table->boolean('ulceras_presion')->default(false);
            $table->text('observaciones')->nullable();

            $table->timestamps();
            $table->foreign('id_historia')->references('id_historia')->on('historias_clinicas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hc_geriatricas');
        Schema::dropIfExists('hc_neurologicas');
        Schema::dropIfExists('hc_quirurgicas');
        Schema::dropIfExists('historias_clinicas');
    }
};
