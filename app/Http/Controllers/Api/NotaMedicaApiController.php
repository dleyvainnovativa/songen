<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveNotaMedicaRequest;
use App\Models\NotaMedica;
use App\Models\Paciente;
use App\Services\NotaMedicaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * API de notas médicas (consumida por las vistas vía window.App).
 */
class NotaMedicaApiController extends Controller
{
    public function __construct(protected NotaMedicaService $notas) {}

    /** POST /api/v1/pacientes/{paciente}/notas */
    public function store(SaveNotaMedicaRequest $request, Paciente $paciente): JsonResponse
    {
        $datos = $request->safe()->except('quirurgica');

        $nota = $this->notas->crear(
            paciente: $paciente,
            datos: $datos,
            idMedico: Auth::id(),
            quirurgica: $request->input('quirurgica', []),
        );

        return response()->json([
            'message' => 'Nota médica creada.',
            'data'    => [
                'id_nota'      => $nota->id_nota,
                'redirect_url' => route('notas.show', [$paciente->id_paciente, $nota->id_nota]),
            ],
        ], 201);
    }

    /** PUT /api/v1/pacientes/{paciente}/notas/{nota} */
    public function update(SaveNotaMedicaRequest $request, Paciente $paciente, NotaMedica $nota): JsonResponse
    {
        abort_unless($nota->id_paciente === $paciente->id_paciente, 404);

        // El servicio lanza ValidationException (422) si la nota está firmada.
        $datos = $request->safe()->except('quirurgica');
        $this->notas->actualizar($nota, $datos, $request->input('quirurgica', []));

        return response()->json([
            'message' => 'Nota actualizada.',
            'data'    => [
                'id_nota'      => $nota->id_nota,
                'redirect_url' => route('notas.show', [$paciente->id_paciente, $nota->id_nota]),
            ],
        ]);
    }

    /** POST /api/v1/pacientes/{paciente}/notas/{nota}/firmar */
    public function firmar(Paciente $paciente, NotaMedica $nota): JsonResponse
    {
        abort_unless($nota->id_paciente === $paciente->id_paciente, 404);

        // Lanza 422 si ya estaba firmada.
        $this->notas->firmar($nota);

        return response()->json([
            'message' => 'Nota firmada. Ahora es de solo lectura.',
            'data'    => [
                'redirect_url' => route('notas.show', [$paciente->id_paciente, $nota->id_nota]),
            ],
        ]);
    }
}
