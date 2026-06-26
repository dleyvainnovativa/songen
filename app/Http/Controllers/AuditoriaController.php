<?php

namespace App\Http\Controllers;

use App\Models\PersonalMedico;
use App\Services\AuditoriaQueryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Visor de auditoría (solo admin — la ruta usa middleware 'firebase:admin').
 */
class AuditoriaController extends Controller
{
    public function __construct(protected AuditoriaQueryService $auditoria) {}

    /** GET /auditoria */
    public function index(Request $request): View
    {
        $registros = $this->auditoria->filtrar($request);
        $medicos   = PersonalMedico::orderBy('primer_apellido')->get();
        $acciones  = AuditoriaQueryService::ACCIONES;

        return view('auditoria.index', compact('registros', 'medicos', 'acciones'));
    }
}
