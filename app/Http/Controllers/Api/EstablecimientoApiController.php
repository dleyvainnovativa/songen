<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEstablecimientoRequest;
use App\Http\Requests\UpdateEstablecimientoRequest;
use App\Models\Establecimiento;
use App\Services\EstablecimientoService;
use Illuminate\Http\JsonResponse;

/**
 * API de establecimientos (solo admin; ruta firebase:admin).
 */
class EstablecimientoApiController extends Controller
{
    public function __construct(protected EstablecimientoService $establecimientos) {}

    /** POST /api/v1/establecimientos */
    public function store(StoreEstablecimientoRequest $request): JsonResponse
    {
        $est = $this->establecimientos->crear($request->validated());

        return response()->json([
            'message' => 'Establecimiento agregado.',
            'data'    => ['id_establecimiento' => $est->id_establecimiento],
        ], 201);
    }

    /** PUT /api/v1/establecimientos/{establecimiento} */
    public function update(UpdateEstablecimientoRequest $request, Establecimiento $establecimiento): JsonResponse
    {
        $this->establecimientos->actualizar($establecimiento, $request->validated());

        return response()->json(['message' => 'Establecimiento actualizado.']);
    }

    /**
     * DELETE /api/v1/establecimientos/{establecimiento}
     * El servicio lanza 422 si tiene pacientes o personal ligados.
     */
    public function destroy(Establecimiento $establecimiento): JsonResponse
    {
        $this->establecimientos->eliminar($establecimiento);

        return response()->json(['message' => 'Establecimiento eliminado.']);
    }
}
