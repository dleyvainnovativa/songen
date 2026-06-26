<?php

namespace App\Http\Controllers;

use App\Models\AuditoriaAcceso;
use App\Models\NotaMedica;
use App\Models\Paciente;
use Illuminate\View\View;

/**
 * Dashboard — resumen de la clínica al iniciar sesión.
 */
class DashboardController extends Controller
{
    /** GET /dashboard */
    public function index(): View
    {
        $stats = [
            'pacientes_activos' => Paciente::where('activo', true)->count(),
            'por_tipo' => [
                'Quirúrgico'  => Paciente::where('activo', true)->where('tipo_paciente', 'Quirúrgico')->count(),
                'Neurológico' => Paciente::where('activo', true)->where('tipo_paciente', 'Neurológico')->count(),
                'Geriátrico'  => Paciente::where('activo', true)->where('tipo_paciente', 'Geriátrico')->count(),
            ],
            'notas_mes' => NotaMedica::whereMonth('fecha_hora', now()->month)
                ->whereYear('fecha_hora', now()->year)->count(),
            'notas_sin_firmar' => NotaMedica::where('firmada', false)->count(),
        ];

        // Pacientes registrados recientemente.
        $recientes = Paciente::where('activo', true)
            ->orderByDesc('id_paciente')
            ->limit(5)
            ->get();
        foreach ($recientes as $key => &$p) {
            $cfg = match ($p->tipo_paciente) {
                'Quirúrgico' => ['#d97706', 'fa-user-doctor'],
                'Neurológico' => ['#0891b2', 'fa-brain'],
                'Geriátrico' => ['#16a34a', 'fa-person-cane'],
                default => ['#64748b', 'fa-user'],
            };
            $ini = mb_strtoupper(mb_substr($p->nombre, 0, 1) . mb_substr($p->primer_apellido, 0, 1));
            $p->cfg = $cfg;
            $p->ini = $ini;
        }

        // Actividad reciente (solo para admin; para médico va vacía).
        $actividad = collect();
        if (auth()->user()->esAdmin()) {
            $actividad = AuditoriaAcceso::with(['medico', 'paciente'])
                ->orderByDesc('fecha_hora')
                ->limit(8)
                ->get();
        }

        return view('dashboard', compact('stats', 'recientes', 'actividad'));
    }
}
