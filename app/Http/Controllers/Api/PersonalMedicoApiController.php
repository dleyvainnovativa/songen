<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePersonalMedicoRequest;
use App\Http\Requests\UpdatePersonalMedicoRequest;
use App\Models\PersonalMedico;
use App\Services\PersonalMedicoService;
use Illuminate\Http\JsonResponse;

/**
 * API de personal médico (solo admin; ruta firebase:admin).
 *
 * Las guardias de seguridad (auto-borrado, último admin, borrar vs archivar)
 * viven en el servicio y se propagan como ValidationException (422).
 */
class PersonalMedicoApiController extends Controller
{
    public function __construct(protected PersonalMedicoService $personal) {}

    /** POST /api/v1/personal */
    public function store(StorePersonalMedicoRequest $request): JsonResponse
    {
        $datos = $request->safe()->except(['crear_acceso', 'password']);

        $credenciales = [];
        if ($request->boolean('crear_acceso')) {
            $credenciales = [
                'email'    => $request->input('email'),
                'password' => $request->input('password'),
            ];
        }

        $res = $this->personal->crear($datos, $credenciales);

        return response()->json([
            'message'        => 'Personal agregado.',
            'firebase_aviso' => $res['firebase'], // null si todo bien
            'data'           => ['id_medico' => $res['medico']->id_medico],
        ], 201);
    }

    /** PUT /api/v1/personal/{personal} */
    public function update(UpdatePersonalMedicoRequest $request, PersonalMedico $personal): JsonResponse
    {
        // El servicio lanza 422 si se intenta quitar admin al último admin.
        $this->personal->actualizar($personal, $request->validated());

        return response()->json(['message' => 'Personal actualizado.']);
    }

    /**
     * DELETE /api/v1/personal/{personal}
     * Borra si no tiene registros ligados; si los tiene, archiva.
     * El servicio lanza 422 ante guardias (auto, último admin).
     */
    public function destroy(PersonalMedico $personal): JsonResponse
    {
        $resultado = $this->personal->eliminarOArchivar($personal);

        return response()->json([
            'message' => $resultado === 'eliminado'
                ? 'Personal eliminado.'
                : 'Tenía registros clínicos ligados, por lo que se archivó en lugar de eliminarse.',
            'resultado' => $resultado,
        ]);
    }

    /** POST /api/v1/personal/{personal}/reactivar */
    public function reactivar(PersonalMedico $personal): JsonResponse
    {
        $this->personal->reactivar($personal);

        return response()->json(['message' => 'Personal reactivado.']);
    }
}
