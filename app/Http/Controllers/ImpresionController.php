<?php

namespace App\Http\Controllers;

use App\Models\HistoriaClinica;
use App\Models\NotaMedica;
use App\Models\Paciente;
use App\Services\AuditoriaService;
use Illuminate\View\View;

/**
 * Vistas de impresión (HC y notas). Cada acceso registra IMPRESION en auditoría.
 *
 * Devuelven una vista con layout propio "print" (sin top-bar, optimizada para
 * papel). El usuario imprime con Ctrl/Cmd+P o el botón que dispara window.print().
 */
class ImpresionController extends Controller
{
    public function __construct(protected AuditoriaService $auditoria) {}

    /** GET /pacientes/{paciente}/historia/imprimir */
    public function historia(Paciente $paciente): View
    {
        $historia = $paciente->historiaClinica;
        abort_unless($historia, 404, 'El paciente no tiene historia clínica.');

        $relacion = HistoriaClinica::relacionSubtipo($paciente->tipo_paciente);
        $subtipo  = $relacion ? $historia->{$relacion} : null;
        $cfg      = config("hc_subtipos.{$paciente->tipo_paciente}");

        $historia->load('medicoResponsable');

        $this->auditoria->impresion(
            tabla: 'historias_clinicas',
            id: $historia->id_historia,
            desc: 'Impresión de historia clínica',
            idPaciente: $paciente->id_paciente,
        );

        return view('impresion.historia', compact('paciente', 'historia', 'subtipo', 'cfg'));
    }

    /** GET /pacientes/{paciente}/notas/{nota}/imprimir */
    public function nota(Paciente $paciente, NotaMedica $nota): View
    {
        abort_unless($nota->id_paciente === $paciente->id_paciente, 404);

        $nota->load(['medico', 'tipoNota', 'notaQuirurgica.cirujano', 'notaQuirurgica.anestesiologo']);

        $this->auditoria->impresion(
            tabla: 'notas_medicas',
            id: $nota->id_nota,
            desc: 'Impresión de nota médica',
            idPaciente: $paciente->id_paciente,
        );

        return view('impresion.nota', compact('paciente', 'nota'));
    }
}
