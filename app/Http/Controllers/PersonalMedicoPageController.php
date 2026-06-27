<?php

namespace App\Http\Controllers;

use App\Models\CatEspecialidad;
use App\Models\Establecimiento;
use App\Models\PersonalMedico;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Página de personal médico (solo admin).
 */
class PersonalMedicoPageController extends Controller
{
    /** GET /personal */
    public function index(Request $request): View
    {
        $q      = trim($request->query('q', ''));
        $estado = $request->query('estado', 'activos'); // activos | inactivos | todos

        $personal = PersonalMedico::query()
            ->with(['establecimiento', 'especialidad'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($s) use ($q) {
                    $s->where('nombre', 'like', "%{$q}%")
                      ->orWhere('primer_apellido', 'like', "%{$q}%")
                      ->orWhere('segundo_apellido', 'like', "%{$q}%")
                      ->orWhere('cedula_profesional', 'like', "%{$q}%");
                });
            })
            ->when($estado === 'activos', fn ($query) => $query->where('activo', true))
            ->when($estado === 'inactivos', fn ($query) => $query->where('activo', false))
            ->orderBy('primer_apellido')
            ->paginate(20)
            ->withQueryString();

        return view('personal.index', [
            'personal'         => $personal,
            'q'                => $q,
            'estado'           => $estado,
            'establecimientos' => Establecimiento::orderBy('nombre')->get(),
            'especialidades'   => CatEspecialidad::orderBy('nombre')->get(),
        ]);
    }
}
