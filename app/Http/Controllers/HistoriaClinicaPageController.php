<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Services\AuditoriaService;
use Illuminate\View\View;

/**
 * Páginas de la Historia Clínica (wizard de 4 pasos).
 *
 * La HC es 1:1 con el paciente, así que no hay índice ni "create vs edit"
 * separados: hay una sola pantalla que crea o edita la HC del paciente.
 * El subtipo (paso 4) se arma desde config/hc_subtipos.php.
 */
class HistoriaClinicaPageController extends Controller
{
    public function __construct(protected AuditoriaService $auditoria) {}

    /** GET /pacientes/{paciente}/historia — wizard de la HC. */
    public function edit(Paciente $paciente): View
    {
        $cfg = config("hc_subtipos.{$paciente->tipo_paciente}");

        // Carga la HC existente y su extensión de subtipo (si existe).
        $historia = $paciente->historiaClinica;
        $subtipo  = null;
        if ($historia) {
            $relacion = \App\Models\HistoriaClinica::relacionSubtipo($paciente->tipo_paciente);
            $subtipo  = $relacion ? $historia->{$relacion} : null;

            $this->auditoria->consulta(
                tabla: 'historias_clinicas',
                id: $historia->id_historia,
                desc: 'Consulta de historia clínica',
                idPaciente: $paciente->id_paciente,
            );
        }

        return view('historias.edit', compact('paciente', 'historia', 'subtipo', 'cfg'));
    }
}
