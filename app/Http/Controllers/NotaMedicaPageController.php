<?php

namespace App\Http\Controllers;

use App\Models\CatTipoNota;
use App\Models\NotaMedica;
use App\Models\Paciente;
use App\Models\PersonalMedico;
use App\Services\AuditoriaService;
use App\Services\NotaMedicaService;
use Illuminate\View\View;

/**
 * Páginas de notas médicas (timeline + alta/edición por paciente).
 */
class NotaMedicaPageController extends Controller
{
    public function __construct(
        protected AuditoriaService $auditoria,
        protected NotaMedicaService $notas,
    ) {}

    /** GET /pacientes/{paciente}/notas — timeline de notas. */
    public function index(Paciente $paciente): View
    {
        $notas = $paciente->notasMedicas()
            ->with(['medico', 'tipoNota'])
            ->orderByDesc('fecha_hora')
            ->get();

        return view('notas.index', compact('paciente', 'notas'));
    }

    /** GET /pacientes/{paciente}/notas/create */
    public function create(Paciente $paciente): View
    {
        return view('notas.create', array_merge(
            ['paciente' => $paciente, 'nota' => null],
            $this->catalogos()
        ));
    }

    /** GET /pacientes/{paciente}/notas/{nota}/edit */
    public function edit(Paciente $paciente, NotaMedica $nota): View
    {
        abort_unless($nota->id_paciente === $paciente->id_paciente, 404);

        // Una nota firmada se ve, pero no se edita: la vista lo refleja.
        $nota->load('notaQuirurgica');

        return view('notas.edit', array_merge(
            ['paciente' => $paciente, 'nota' => $nota],
            $this->catalogos()
        ));
    }

    /** GET /pacientes/{paciente}/notas/{nota} — vista de solo lectura. */
    public function show(Paciente $paciente, NotaMedica $nota): View
    {
        abort_unless($nota->id_paciente === $paciente->id_paciente, 404);

        $nota->load(['medico', 'tipoNota', 'notaQuirurgica.cirujano', 'notaQuirurgica.anestesiologo']);

        $sv = collect([
            'T/A' => $nota->presion_arterial,
            'FC' => $nota->frecuencia_cardiaca,
            'FR' => $nota->frecuencia_respiratoria,
            'Temp' => $nota->temperatura_c,
            'SatO₂' => $nota->saturacion_o2,
            'Peso' => $nota->peso_kg,
        ])->filter(fn($v) => $v !== null && $v !== '');

        $firmada = $nota->firmada;


        $this->auditoria->consulta(
            tabla: 'notas_medicas',
            id: $nota->id_nota,
            desc: 'Consulta de nota médica',
            idPaciente: $paciente->id_paciente,
        );

        // $data["nota"] =

        return view('notas.show', compact('paciente', 'nota', 'sv', 'firmada'));
    }

    private function catalogos(): array
    {
        return [
            'tiposNota' => CatTipoNota::orderBy('id_tipo_nota')->get(),
            'medicos'   => PersonalMedico::where('activo', true)->orderBy('primer_apellido')->get(),
        ];
    }
}
