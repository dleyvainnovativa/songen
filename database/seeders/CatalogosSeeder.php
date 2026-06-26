<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Siembra los catálogos base (cat_*). Idempotente: usa updateOrInsert,
 * así correrlo de nuevo no duplica filas.
 */
class CatalogosSeeder extends Seeder
{
    public function run(): void
    {
        $this->sembrar('cat_escolaridades', 'id_escolaridad', 'descripcion', [
            'Ninguna', 'Primaria', 'Secundaria', 'Bachillerato',
            'Técnico', 'Licenciatura', 'Posgrado', 'Otro',
        ]);

        $this->sembrar('cat_estados_civiles', 'id_estado_civil', 'descripcion', [
            'Soltero(a)', 'Casado(a)', 'Unión libre',
            'Divorciado(a)', 'Viudo(a)', 'Separado(a)',
        ]);

        $this->sembrar('cat_tipos_sangre', 'id_tipo_sangre', 'descripcion', [
            'O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-',
        ]);

        $this->sembrar('cat_estados_cita', 'id_estado_cita', 'descripcion', [
            'Programada', 'Confirmada', 'En curso',
            'Completada', 'Cancelada',
        ]);

        $this->sembrar('cat_tipos_nota', 'id_tipo_nota', 'descripcion', [
            'Evolución', 'Ingreso', 'Interconsulta', 'Urgencias',
            'Preoperatoria', 'Postoperatoria', 'Egreso', 'Indicaciones',
        ]);

        // Especialidades: con bandera cedula_requerida
        $especialidades = [
            ['nombre' => 'Fisioterapia',        'cedula_requerida' => 1],
            ['nombre' => 'Traumatología',       'cedula_requerida' => 1],
            ['nombre' => 'Neurología',          'cedula_requerida' => 1],
            ['nombre' => 'Geriatría',           'cedula_requerida' => 1],
            ['nombre' => 'Ortopedia',           'cedula_requerida' => 1],
            ['nombre' => 'Rehabilitación',      'cedula_requerida' => 1],
            ['nombre' => 'Medicina General',    'cedula_requerida' => 1],
            ['nombre' => 'Enfermería',          'cedula_requerida' => 0],
        ];
        foreach ($especialidades as $i => $row) {
            DB::table('cat_especialidades')->updateOrInsert(
                ['id_especialidad' => $i + 1],
                $row
            );
        }
    }

    private function sembrar(string $tabla, string $pk, string $col, array $valores): void
    {
        foreach ($valores as $i => $valor) {
            DB::table($tabla)->updateOrInsert([$pk => $i + 1], [$col => $valor]);
        }
    }
}
