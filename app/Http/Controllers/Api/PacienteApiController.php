<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePacienteRequest;
use App\Http\Requests\UpdatePacienteRequest;
use App\Models\Paciente;
use App\Services\AuditoriaService;
use App\Services\PacienteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API de pacientes (consumida por las vistas vía window.App).
 *
 * Devuelve JSON. La auditoría de CREACION/MODIFICACION/ELIMINACION la dispara
 * el AuditableObserver automáticamente; aquí solo orquestamos.
 */
class PacienteApiController extends Controller
{
    public function __construct(
        protected PacienteService $pacientes,
        protected AuditoriaService $auditoria,
    ) {}

    /** POST /api/v1/pacientes */
    public function store(StorePacienteRequest $request): JsonResponse
    {
        $datos = $request->safe()->except(['contactos', 'medicamentos']);
        $paciente = $this->pacientes->crear(
            $datos,
            $request->input('contactos', []),
            $request->input('medicamentos', []),
        );

        return response()->json([
            'message' => 'Paciente registrado correctamente.',
            'data'    => [
                'id_paciente'  => $paciente->id_paciente,
                'redirect_url' => route('pacientes.show', $paciente->id_paciente),
            ],
        ], 201);
    }

    /** PUT /api/v1/pacientes/{paciente} */
    public function update(UpdatePacienteRequest $request, Paciente $paciente): JsonResponse
    {
        // Guardia: no permitir cambiar tipo_paciente si ya hay historia clínica,
        // porque la extensión (hc_*) quedaría inconsistente con el nuevo tipo.
        if (
            $paciente->historiaClinica()->exists() &&
            $request->input('tipo_paciente') !== $paciente->tipo_paciente
        ) {
            return response()->json([
                'message' => 'No se puede cambiar el tipo de paciente: ya tiene historia clínica registrada.',
                'errors'  => ['tipo_paciente' => ['Bloqueado por historia clínica existente.']],
            ], 422);
        }

        $datos = $request->safe()->except(['contactos', 'medicamentos']);
        $this->pacientes->actualizar(
            $paciente,
            $datos,
            $request->input('contactos', []),
            $request->input('medicamentos', []),
        );

        return response()->json([
            'message' => 'Paciente actualizado correctamente.',
            'data'    => [
                'id_paciente'  => $paciente->id_paciente,
                'redirect_url' => route('pacientes.show', $paciente->id_paciente),
            ],
        ]);
    }

    /** DELETE /api/v1/pacientes/{paciente} — baja lógica */
    public function destroy(Paciente $paciente): JsonResponse
    {
        $this->pacientes->desactivar($paciente);

        return response()->json([
            'message' => 'Paciente dado de baja.',
        ]);
    }

    /** POST /api/v1/pacientes/{paciente}/reactivar */
    public function reactivar(Paciente $paciente): JsonResponse
    {
        $this->pacientes->reactivar($paciente);

        return response()->json([
            'message' => 'Paciente reactivado.',
        ]);
    }
}
