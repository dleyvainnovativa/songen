<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Citas / Agenda ─────────────────────────────────────────────────────
        Schema::create('citas', function (Blueprint $table) {
            $table->increments('id_cita');
            $table->unsignedInteger('id_paciente');
            $table->unsignedInteger('id_medico');
            $table->unsignedSmallInteger('id_establecimiento');
            $table->unsignedTinyInteger('id_estado_cita')->default(1);  // Programada
            $table->enum('tipo_paciente', ['Quirúrgico', 'Neurológico', 'Geriátrico']);
            $table->enum('tipo_cita', [
                'Primera vez', 'Subsecuente', 'Control',
                'Urgencia', 'Pre-quirúrgica', 'Post-quirúrgica',
            ]);
            $table->dateTime('fecha_hora_cita');
            $table->unsignedTinyInteger('duracion_min')->default(30);
            $table->string('motivo', 255)->nullable();
            $table->text('indicaciones_previas')->nullable();   // Ayuno, estudios previos…
            $table->string('notas_admin', 255)->nullable();
            $table->unsignedInteger('creado_por')->nullable();
            $table->string('cancelacion_motivo', 255)->nullable();
            $table->timestamps();

            $table->foreign('id_paciente')->references('id_paciente')->on('pacientes');
            $table->foreign('id_medico')->references('id_medico')->on('personal_medico');
            $table->foreign('id_establecimiento')->references('id_establecimiento')->on('establecimientos');
            $table->foreign('id_estado_cita')->references('id_estado_cita')->on('cat_estados_cita');

            // Índices para la agenda
            $table->index(['id_medico', 'fecha_hora_cita'], 'idx_cita_medico_fecha');
            $table->index('fecha_hora_cita', 'idx_cita_fecha');
        });

        // ── Recordatorios de cita ──────────────────────────────────────────────
        Schema::create('cita_recordatorios', function (Blueprint $table) {
            $table->increments('id_recordatorio');
            $table->unsignedInteger('id_cita');
            $table->enum('canal', ['SMS', 'Email', 'App']);
            $table->dateTime('fecha_programada');
            $table->dateTime('fecha_enviado')->nullable();
            $table->boolean('enviado')->default(false);
            $table->timestamps();

            $table->foreign('id_cita')->references('id_cita')->on('citas')->onDelete('cascade');
        });

        // ── Referencia / Traslado (NOM-004 §7.5) ──────────────────────────────
        Schema::create('referencias_traslados', function (Blueprint $table) {
            $table->increments('id_referencia');
            $table->unsignedInteger('id_paciente');
            $table->unsignedInteger('id_medico_remite');
            $table->string('establecimiento_destino', 200);
            $table->text('motivo');
            $table->text('resumen_clinico');
            $table->text('estado_paciente')->nullable();
            $table->text('diagnostico_envio');
            $table->dateTime('fecha_hora')->useCurrent();
            $table->enum('tipo', ['Referencia', 'Traslado']);
            $table->timestamps();

            $table->foreign('id_paciente')->references('id_paciente')->on('pacientes');
            $table->foreign('id_medico_remite')->references('id_medico')->on('personal_medico');
        });

        // ── Nota de Egreso (NOM-004 §7.8) ─────────────────────────────────────
        Schema::create('notas_egreso', function (Blueprint $table) {
            $table->increments('id_egreso');
            $table->unsignedInteger('id_paciente');
            $table->unsignedInteger('id_medico');
            $table->date('fecha_ingreso');
            $table->date('fecha_egreso');
            $table->text('motivo_ingreso');
            $table->text('resumen_clinico');
            $table->text('diagnostico_final');
            $table->text('procedimientos_realizados')->nullable();
            $table->enum('condicion_egreso', [
                'Mejoría', 'Curación', 'Defunción', 'Voluntario', 'Traslado',
            ]);
            $table->text('pronostico')->nullable();
            $table->text('indicaciones_alta')->nullable();
            $table->date('cita_seguimiento')->nullable();
            $table->timestamps();

            $table->foreign('id_paciente')->references('id_paciente')->on('pacientes');
            $table->foreign('id_medico')->references('id_medico')->on('personal_medico');
        });

        // ── Auditoría / Trazabilidad (NOM-004 §5.3 — confidencialidad) ─────────
        Schema::create('auditoria_accesos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_auditoria')->autoIncrement()->primary();
            $table->unsignedInteger('id_medico')->nullable();
            $table->unsignedInteger('id_paciente')->nullable();
            $table->enum('accion', [
                'CONSULTA', 'CREACION', 'MODIFICACION', 'ELIMINACION', 'IMPRESION',
            ]);
            $table->string('tabla_afectada', 60)->nullable();
            $table->unsignedInteger('id_registro')->nullable();
            $table->string('descripcion', 500)->nullable();
            $table->string('ip_origen', 45)->nullable();
            $table->dateTime('fecha_hora')->useCurrent();

            // No FK obligatoria para auditoría (registros de usuarios ya eliminados deben conservarse)
            $table->index('fecha_hora', 'idx_auditoria_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria_accesos');
        Schema::dropIfExists('notas_egreso');
        Schema::dropIfExists('referencias_traslados');
        Schema::dropIfExists('cita_recordatorios');
        Schema::dropIfExists('citas');
    }
};
