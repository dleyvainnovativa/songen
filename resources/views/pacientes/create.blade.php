{{--
    Vista: pacientes/create.blade.php
    Ruta:  GET /pacientes/create
    Submit: POST /api/v1/pacientes vía window.App
--}}
@extends('main')

@section('title', 'Nuevo paciente · Songen')

@section('content')
<div class="mb-3">
    <a href="{{ route('pacientes.index') }}" class="text-decoration-none small text-muted">
        <i class="fa-solid fa-arrow-left"></i> Pacientes
    </a>
    <h1 class="h5 mb-0 mt-1" style="font-weight:700;color:var(--slate)">Nuevo paciente</h1>
</div>

@include('pacientes._form', ['paciente' => null])
@endsection

@push('scripts')
@vite('resources/js/pacientes-form.js')
@endpush