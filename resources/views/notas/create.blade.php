{{--
    Vista: notas/create.blade.php — GET /pacientes/{paciente}/notas/create
--}}
@extends('main')
@section('title', 'Nueva nota · ' . $paciente->nombre_completo)
@section('content')
    <div class="mb-3">
        <a href="{{ route('notas.index', $paciente->id_paciente) }}" class="text-decoration-none small text-muted">
            <i class="fa-solid fa-arrow-left"></i> Notas de {{ $paciente->nombre_completo }}
        </a>
        <h1 class="h5 mb-0 mt-1" style="font-weight:700;color:var(--slate)">Nueva nota médica</h1>
    </div>
    @include('notas._form', ['nota' => null])
@endsection
@push('scripts')
    @vite('resources/js/notas-form.js')
@endpush
