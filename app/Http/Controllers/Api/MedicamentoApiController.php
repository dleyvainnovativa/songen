<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicamentoRequest;
use App\Http\Requests\UpdateMedicamentoRequest;
use App\Models\Medicamento;
use App\Services\MedicamentoService;
use Illuminate\Http\JsonResponse;

/**
 * API del catálogo de medicamentos (solo admin; ruta firebase:admin).
 */
class MedicamentoApiController extends Controller
{
    public function __construct(protected MedicamentoService $medicamentos) {}

    /** POST /api/v1/medicamentos */
    public function store(StoreMedicamentoRequest $request): JsonResponse
    {
        $med = $this->medicamentos->crear($request->validated());

        return response()->json([
            'message' => 'Medicamento agregado.',
            'data'    => ['id_medicamento' => $med->id_medicamento],
        ], 201);
    }

    /** PUT /api/v1/medicamentos/{medicamento} */
    public function update(UpdateMedicamentoRequest $request, Medicamento $medicamento): JsonResponse
    {
        $this->medicamentos->actualizar($medicamento, $request->validated());

        return response()->json(['message' => 'Medicamento actualizado.']);
    }

    /** DELETE /api/v1/medicamentos/{medicamento} — archiva */
    public function destroy(Medicamento $medicamento): JsonResponse
    {
        $this->medicamentos->archivar($medicamento);

        return response()->json(['message' => 'Medicamento archivado.']);
    }

    /** POST /api/v1/medicamentos/{medicamento}/reactivar */
    public function reactivar(Medicamento $medicamento): JsonResponse
    {
        $this->medicamentos->reactivar($medicamento);

        return response()->json(['message' => 'Medicamento reactivado.']);
    }
}
