<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentoRequest;
use App\Models\Paciente;
use App\Models\PacienteDocumento;
use App\Services\AuditoriaService;
use App\Services\DocumentoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Documentos del paciente. La subida es POST multipart con redirect (más
 * confiable que fetch para archivos en hosting compartido); el borrado también
 * es POST con confirmación previa en el cliente.
 */
class DocumentoController extends Controller
{
    public function __construct(
        protected DocumentoService $documentos,
        protected AuditoriaService $auditoria,
    ) {}

    /** GET /pacientes/{paciente}/documentos */
    public function index(Paciente $paciente): View
    {
        $documentos = $paciente->documentos()
            ->with('medico')
            ->orderByDesc('created_at')
            ->get();

        return view('documentos.index', compact('paciente', 'documentos'));
    }

    /** POST /pacientes/{paciente}/documentos */
    public function store(StoreDocumentoRequest $request, Paciente $paciente): RedirectResponse
    {
        $this->documentos->subir(
            paciente: $paciente,
            archivo: $request->file('archivo'),
            meta: $request->safe()->only(['titulo', 'categoria', 'notas']),
            idMedico: Auth::id(),
        );

        return redirect()
            ->route('documentos.index', $paciente->id_paciente)
            ->with('ok', 'Documento subido correctamente.');
    }

    /** POST /pacientes/{paciente}/documentos/{documento}/eliminar */
    public function destroy(Paciente $paciente, PacienteDocumento $documento): RedirectResponse
    {
        abort_unless($documento->id_paciente === $paciente->id_paciente, 404);

        $this->documentos->eliminar($documento);

        return redirect()
            ->route('documentos.index', $paciente->id_paciente)
            ->with('ok', 'Documento eliminado.');
    }
}
