{{--
    Vista: notas/edit.blade.php — GET /pacientes/{paciente}/notas/{nota}/edit
    Una nota firmada no es editable: si llega firmada, mejor mandar a show.
--}}
@extends('main')
@section('title', 'Editar nota · ' . $paciente->nombre_completo)
@section('content')
    @if($nota->firmada)
        <div class="alert-clinico mb-3">
            <i class="fa-solid fa-lock"></i>
            <div>Esta nota está <strong>firmada</strong> y no puede editarse.
                <a href="{{ route('notas.show', [$paciente->id_paciente, $nota->id_nota]) }}">Verla</a>.
            </div>
        </div>
    @else
        <div class="mb-3">
            <a href="{{ route('notas.index', $paciente->id_paciente) }}" class="text-decoration-none small text-muted">
                <i class="fa-solid fa-arrow-left"></i> Notas de {{ $paciente->nombre_completo }}
            </a>
            <h1 class="h5 mb-0 mt-1" style="font-weight:700;color:var(--slate)">Editar nota médica</h1>
        </div>
        @include('notas._form', ['nota' => $nota])
    @endif
@endsection
@push('scripts')
    @vite('resources/js/notas-form.js')
@endpush
