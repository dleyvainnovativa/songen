<?php

namespace App\Services;

use App\Models\AuditoriaAcceso;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

/**
 * AuditoriaQueryService — lecturas del log de auditoría para el visor (Fase 5).
 *
 * AuditoriaService (Fase 0) ESCRIBE la bitácora; este servicio la LEE y filtra.
 * Separados a propósito: escritura y consulta tienen responsabilidades distintas.
 */
class AuditoriaQueryService
{
    /** Acciones posibles, para poblar el filtro. */
    public const ACCIONES = ['CONSULTA', 'CREACION', 'MODIFICACION', 'ELIMINACION', 'IMPRESION'];

    /**
     * Construye la consulta filtrada y paginada del log.
     *
     * Filtros soportados (query string):
     *   q          texto en descripción / tabla
     *   medico     id_medico
     *   paciente   id_paciente
     *   accion     una de ACCIONES
     *   desde/hasta  rango de fechas (Y-m-d)
     */
    public function filtrar(Request $request): LengthAwarePaginator
    {
        return AuditoriaAcceso::query()
            ->with(['medico', 'paciente'])
            ->when($request->filled('q'), function ($qq) use ($request) {
                $t = trim($request->query('q'));
                $qq->where(function ($s) use ($t) {
                    $s->where('descripcion', 'like', "%{$t}%")
                      ->orWhere('tabla_afectada', 'like', "%{$t}%");
                });
            })
            ->when($request->filled('medico'), fn ($qq) => $qq->where('id_medico', $request->query('medico')))
            ->when($request->filled('paciente'), fn ($qq) => $qq->where('id_paciente', $request->query('paciente')))
            ->when($request->filled('accion'), fn ($qq) => $qq->where('accion', $request->query('accion')))
            ->when($request->filled('desde'), fn ($qq) => $qq->whereDate('fecha_hora', '>=', $request->query('desde')))
            ->when($request->filled('hasta'), fn ($qq) => $qq->whereDate('fecha_hora', '<=', $request->query('hasta')))
            ->orderByDesc('fecha_hora')
            ->paginate(30)
            ->withQueryString();
    }
}
