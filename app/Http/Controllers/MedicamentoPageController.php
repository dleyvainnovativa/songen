<?php

namespace App\Http\Controllers;

use App\Models\Medicamento;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Página del catálogo de medicamentos.
 *
 * La vista la pueden ver todos los médicos; las acciones de gestión (crear/
 * editar/archivar) solo aparecen para admin y van por rutas firebase:admin.
 */
class MedicamentoPageController extends Controller
{
    /** GET /medicamentos */
    public function index(Request $request): View
    {
        $q      = trim($request->query('q', ''));
        $estado = $request->query('estado', 'activos'); // activos | inactivos | todos

        $medicamentos = Medicamento::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($s) use ($q) {
                    $s->where('nombre_generico', 'like', "%{$q}%")
                      ->orWhere('nombre_comercial', 'like', "%{$q}%");
                });
            })
            ->when($estado === 'activos', fn ($query) => $query->where('activo', true))
            ->when($estado === 'inactivos', fn ($query) => $query->where('activo', false))
            ->orderBy('nombre_generico')
            ->paginate(20)
            ->withQueryString();

        return view('medicamentos.index', compact('medicamentos', 'q', 'estado'));
    }
}
