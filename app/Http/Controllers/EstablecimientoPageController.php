<?php

namespace App\Http\Controllers;

use App\Models\Establecimiento;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Página de establecimientos. Lectura para todos; gestión solo admin
 * (botones + rutas firebase:admin).
 */
class EstablecimientoPageController extends Controller
{
    /** GET /establecimientos */
    public function index(Request $request): View
    {
        $q = trim($request->query('q', ''));

        $establecimientos = Establecimiento::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('nombre', 'like', "%{$q}%")
                      ->orWhere('municipio', 'like', "%{$q}%")
                      ->orWhere('estado', 'like', "%{$q}%");
            })
            ->withCount(['pacientes', 'personalMedico'])
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();

        return view('establecimientos.index', compact('establecimientos', 'q'));
    }
}
