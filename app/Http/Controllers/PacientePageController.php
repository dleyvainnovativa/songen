<?php

namespace App\Http\Controllers;

use App\Models\CatEscolaridad;
use App\Models\CatEstadoCivil;
use App\Models\CatTipoSangre;
use App\Models\Establecimiento;
use App\Models\Medicamento;
use App\Models\Paciente;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controlador de páginas (Blade) de pacientes.
 *
 * Las mutaciones (crear/editar/baja) las hace PacienteApiController vía fetch;
 * aquí solo servimos las vistas y resolvemos los catálogos que necesitan.
 */
class PacientePageController extends Controller
{
    public function __construct(protected AuditoriaService $auditoria) {}

    /** GET /pacientes — listado con búsqueda y filtro por tipo. */
    public function index(Request $request): View
    {
        $q     = trim($request->query('q', ''));
        $tipo  = $request->query('tipo');
        $estado = $request->query('estado', 'activos'); // activos | inactivos | todos

        $pacientes = Paciente::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nombre', 'like', "%{$q}%")
                        ->orWhere('primer_apellido', 'like', "%{$q}%")
                        ->orWhere('segundo_apellido', 'like', "%{$q}%")
                        ->orWhere('numero_expediente', 'like', "%{$q}%")
                        ->orWhere('curp', 'like', "%{$q}%");
                });
            })
            ->when($tipo, fn ($query) => $query->where('tipo_paciente', $tipo))
            ->when($estado === 'activos', fn ($query) => $query->where('activo', true))
            ->when($estado === 'inactivos', fn ($query) => $query->where('activo', false))
            ->orderBy('primer_apellido')
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('pacientes.index', compact('pacientes', 'q', 'tipo', 'estado'));
    }

    /** GET /pacientes/create */
    public function create(): View
    {
        return view('pacientes.create', $this->catalogos());
    }

    /** GET /pacientes/{paciente} */
    public function show(Paciente $paciente): View
    {
        $paciente->load([
            'establecimiento', 'estadoCivil', 'escolaridad', 'tipoSangre',
            'contactosEmergencia', 'medicamentos.medicamento',
            'historiaClinica',
        ]);

        $this->auditoria->consulta(
            tabla: 'pacientes',
            id: $paciente->id_paciente,
            desc: 'Consulta de expediente',
            idPaciente: $paciente->id_paciente,
        );

        return view('pacientes.show', compact('paciente'));
    }

    /** GET /pacientes/{paciente}/edit */
    public function edit(Paciente $paciente): View
    {
        $paciente->load(['contactosEmergencia', 'medicamentos']);

        return view('pacientes.edit', array_merge(
            $this->catalogos(),
            ['paciente' => $paciente]
        ));
    }

    /** Catálogos compartidos por create/edit. */
    private function catalogos(): array
    {
        return [
            'establecimientos' => Establecimiento::orderBy('nombre')->get(),
            'estadosCiviles'   => CatEstadoCivil::orderBy('id_estado_civil')->get(),
            'escolaridades'    => CatEscolaridad::orderBy('id_escolaridad')->get(),
            'tiposSangre'      => CatTipoSangre::orderBy('id_tipo_sangre')->get(),
            'medicamentos'     => Medicamento::where('activo', true)->orderBy('nombre_generico')->get(),
        ];
    }
}
