<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveHistoriaClinicaRequest;
use App\Models\Paciente;
use App\Services\HistoriaClinicaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * API de la Historia Clínica (consumida por el wizard vía window.App).
 */
class HistoriaClinicaApiController extends Controller
{
    public function __construct(protected HistoriaClinicaService $historias) {}

    /** PUT /api/v1/pacientes/{paciente}/historia */
    public function save(SaveHistoriaClinicaRequest $request, Paciente $paciente): JsonResponse
    {
        $validado = $request->validated();

        // Separa el grupo subtipo del resto (campos del padre).
        $subtipo = $validado['subtipo'] ?? [];
        unset($validado['subtipo']);

        $historia = $this->historias->guardar(
            paciente: $paciente,
            datosHc: $validado,
            datosSubtipo: $subtipo,
            idMedico: Auth::id(),
        );

        return response()->json([
            'message' => 'Historia clínica guardada correctamente.',
            'data'    => [
                'id_historia'  => $historia->id_historia,
                'imc'          => $historia->imc,
                'redirect_url' => route('pacientes.show', $paciente->id_paciente),
            ],
        ]);
    }
}
