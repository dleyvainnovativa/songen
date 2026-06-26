{{--
    Vista: documentos/index.blade.php
    Ruta:  GET /pacientes/{paciente}/documentos

    Lista de documentos del paciente + formulario de subida. La subida es POST
    multipart normal (con redirect), no fetch, por confiabilidad en hosting.

    Variables: $paciente, $documentos (colección)
--}}
@extends('main')

@section('title', 'Documentos · ' . $paciente->nombre_completo)

@section('content')
    <div class="mb-3">
        <a href="{{ route('pacientes.show', $paciente->id_paciente) }}" class="text-decoration-none small text-muted">
            <i class="fa-solid fa-arrow-left"></i> {{ $paciente->nombre_completo }}
        </a>
        <h1 class="h5 mb-0 mt-1" style="font-weight:700;color:var(--slate)">Documentos</h1>
    </div>

    @if(session('ok'))
        <div class="alert alert-success py-2 px-3 small">{{ session('ok') }}</div>
    @endif
    @if($errors->any())
        <div class="alert-clinico mb-3">
            <i class="fa-solid fa-circle-exclamation"></i>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    <div class="row g-3">
        {{-- Formulario de subida --}}
        <div class="col-lg-4">
            <div class="sec-card">
                <div class="sec-header">
                    <div class="sec-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                    <div><p class="sec-title">Subir documento</p></div>
                </div>
                <div class="sec-body">
                    <form method="POST" action="{{ route('documentos.store', $paciente->id_paciente) }}"
                          enctype="multipart/form-data" id="doc-form">
                        @csrf

                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título <span class="req">*</span></label>
                            <input type="text" id="titulo" name="titulo" class="form-control"
                                   value="{{ old('titulo') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="categoria" class="form-label">Categoría</label>
                            <select id="categoria" name="categoria" class="form-select">
                                <option value="">—</option>
                                @foreach(['Estudio','Receta','Consentimiento','Laboratorio','Imagenología','Otro'] as $cat)
                                    <option value="{{ $cat }}" @selected(old('categoria') === $cat)>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Dropzone --}}
                        <div class="mb-3">
                            <label class="form-label">Archivo <span class="req">*</span></label>
                            <label class="dropzone" id="dropzone">
                                <input type="file" name="archivo" id="archivo" class="dropzone-input"
                                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" required>
                                <i class="fa-solid fa-cloud-arrow-up dropzone-icon"></i>
                                <span class="dropzone-text" id="dropzone-text">
                                    Toca para elegir o arrastra aquí
                                </span>
                                <span class="dropzone-hint">PDF, imagen u Office · máx 10 MB</span>
                            </label>
                        </div>

                        <div class="mb-3">
                            <label for="notas" class="form-label">Notas</label>
                            <textarea id="notas" name="notas" class="form-control" rows="2">{{ old('notas') }}</textarea>
                        </div>

                        <button type="submit" class="btn-next w-100 justify-content-center" id="btn-subir">
                            <i class="fa-solid fa-upload"></i> Subir documento
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Lista de documentos --}}
        <div class="col-lg-8">
            @if($documentos->isEmpty())
                <div class="sec-card">
                    <div class="sec-body text-center py-5">
                        <i class="fa-solid fa-folder-open fa-2x mb-2" style="color:var(--slate-mid)"></i>
                        <p class="mb-0" style="font-weight:600">Sin documentos</p>
                        <p class="text-muted small mb-0">Sube el primer documento de este paciente.</p>
                    </div>
                </div>
            @else
                <div class="doc-grid">
                    @foreach($documentos as $doc)
                        <div class="doc-card">
                            <a href="{{ $doc->url }}" target="_blank" class="doc-card-main">
                                <div class="doc-icon doc-icon-{{ $doc->es_imagen ? 'img' : 'file' }}">
                                    <i class="fa-solid {{ $doc->icono }}"></i>
                                </div>
                                <div class="doc-info">
                                    <div class="doc-title">{{ $doc->titulo }}</div>
                                    <div class="doc-meta">
                                        @if($doc->categoria)<span class="doc-cat">{{ $doc->categoria }}</span>@endif
                                        <span>{{ $doc->tamano_legible }}</span>
                                    </div>
                                    <div class="doc-sub">
                                        {{ $doc->created_at?->format('d/m/Y') }} ·
                                        {{ $doc->medico->nombre_completo ?? 'Sistema' }}
                                    </div>
                                </div>
                            </a>
                            <form method="POST"
                                  action="{{ route('documentos.destroy', [$paciente->id_paciente, $doc->id_documento]) }}"
                                  onsubmit="return confirm('¿Eliminar este documento? Esta acción no se puede deshacer.')"
                                  class="doc-delete">
                                @csrf
                                <button type="submit" class="btn-icon btn-icon-danger" title="Eliminar">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Dropzone: muestra el nombre del archivo elegido y soporta drag & drop.
    (function () {
        const dz = document.getElementById('dropzone');
        const input = document.getElementById('archivo');
        const text = document.getElementById('dropzone-text');
        if (!dz || !input) return;

        input.addEventListener('change', () => {
            if (input.files.length) {
                text.textContent = input.files[0].name;
                dz.classList.add('has-file');
            }
        });

        ['dragover', 'dragenter'].forEach(ev =>
            dz.addEventListener(ev, e => { e.preventDefault(); dz.classList.add('dragging'); }));
        ['dragleave', 'drop'].forEach(ev =>
            dz.addEventListener(ev, e => { e.preventDefault(); dz.classList.remove('dragging'); }));
        dz.addEventListener('drop', e => {
            if (e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                input.dispatchEvent(new Event('change'));
            }
        });

        // Estado de carga al enviar.
        document.getElementById('doc-form').addEventListener('submit', () => {
            const btn = document.getElementById('btn-subir');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Subiendo…';
        });
    })();
</script>
@endpush
