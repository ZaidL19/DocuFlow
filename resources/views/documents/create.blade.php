@extends('layouts.app')
@section('title', 'Registrar Documento')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="fw-bold mb-0">Registro de Documentos</h3>
        <p class="text-muted small mb-0">Elige entre extraccion automatica mediante IA o ingreso manual de metadatos.</p>
    </div>
</div>

<ul class="nav nav-pills mb-3" id="uploadModeTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-semibold" id="ai-tab" data-bs-toggle="pill" data-bs-target="#ai-pane" type="button" role="tab">
            <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Carga con IA
        </button>
    </li>
    <li class="nav-item ms-2" role="presentation">
        <button class="nav-link fw-semibold" id="manual-tab" data-bs-toggle="pill" data-bs-target="#manual-pane" type="button" role="tab">
            <i class="fa-solid fa-keyboard me-1"></i> Carga Manual
        </button>
    </li>
</ul>

<div class="tab-content" id="uploadModeTabsContent">
    <!-- PESTAÑA 1: CARGA CON IA -->
    <div class="tab-pane fade show active" id="ai-pane" role="tabpanel">
        <div class="card p-4" style="max-width: 680px;">
            <form method="POST" action="{{ route('documents.processAi') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Categoria Administrativa (Opcional)</label>
                    <select name="category_id" class="form-select">
                        <option value="">-- Detectar / Sin categoria inicial --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Archivo Digitalizado (PDF, JPG, PNG - Max 10MB)</label>
                    <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                    <i class="fa-solid fa-microchip me-2"></i> Procesar Documento con IA
                </button>
            </form>
        </div>
    </div>

    <!-- PESTAÑA 2: CARGA MANUAL -->
    <div class="tab-pane fade" id="manual-pane" role="tabpanel">
        <div class="card p-4" style="max-width: 880px;">
            <form method="POST" action="{{ route('documents.storeManual') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Codigo del Documento *</label>
                        <input type="text" name="documentCode" class="form-control" placeholder="Ej. OFICIO N° 051-2026-UNS" value="{{ old('documentCode') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tipo de Documento *</label>
                        <select name="documentType" class="form-select" required>
                            @foreach(['Oficio', 'Contrato', 'Memorando', 'Resolucion', 'Solicitud', 'Informe'] as $type)
                                <option value="{{ $type }}" @selected(old('documentType') === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Fecha de Emision *</label>
                        <input type="date" name="issueDate" class="form-control" value="{{ old('issueDate', date('Y-m-d')) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Remitente / Dependencia *</label>
                        <input type="text" name="sender" class="form-control" placeholder="Ej. Direccion de Escuela EPISI" value="{{ old('sender') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Destinatario *</label>
                        <input type="text" name="recipient" class="form-control" placeholder="Ej. Rectorado UNS" value="{{ old('recipient') }}" required>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Asunto *</label>
                        <input type="text" name="subject" class="form-control" placeholder="Sumilla o asunto principal del documento" value="{{ old('subject') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Categoria</label>
                        <select name="category_id" class="form-select">
                            <option value="">-- Seleccionar --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Palabras Clave</label>
                        <input type="text" name="keywords" class="form-control" placeholder="Ej. tramite, resolucion, sistemas" value="{{ old('keywords') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Archivo Digitalizado (PDF, JPG, PNG - Max 10MB) *</label>
                        <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Resumen / Observaciones</label>
                        <textarea name="summary" class="form-control" rows="2" placeholder="Descripcion breve opcional">{{ old('summary') }}</textarea>
                    </div>
                </div>

                <div class="mt-3 d-flex justify-content-end gap-2">
                    <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-success px-4 fw-semibold">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Registro Manual
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection