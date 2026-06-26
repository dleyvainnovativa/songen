{{--
    Vista: pacientes/edit.blade.php
    Ruta:  GET /pacientes/{paciente}/edit
    Submit: PUT /api/v1/pacientes/{id} vía window.App
--}}
@extends('main')

@section('title', 'Editar paciente · Songen')

@section('content')
<div class="mb-3">
    <a href="{{ route('pacientes.show', $paciente->id_paciente) }}" class="text-decoration-none small text-muted">
        <i class="fa-solid fa-arrow-left"></i> {{ $paciente->nombre_completo }}
    </a>
    <h1 class="h5 mb-0 mt-1" style="font-weight:700;color:var(--slate)">Editar paciente</h1>
</div>

@include('pacientes._form', ['paciente' => $paciente])
@endsection

@push('scripts')
@vite('resources/js/pacientes-form.js')
@endpush