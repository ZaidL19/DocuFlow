@extends('layouts.app')
@section('title', 'Revisar Extraccion IA')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="fw-bold mb-0">Revision y Validacion de Metadatos Extraidos</h3>
        <p class="text-muted small mb-0">Corrige cualquier campo antes de guardar definitivamente en el repositorio.</p>
    </div>
    @if($latestAiLog)
        <span class="badge bg-info text-dark fs-6">
            Modelo: {{ $latestAiLog->modelUsed }} | Tiempo: {{ $latestAiLog->processingTimeMs }} ms
        </span>
    @endif
</div>

<div class="row g-4">
    <!-- Visor del Documento -->
    <div class="col-lg-6">
        <div class="card p-3 h-100">
            <h6 class="fw-bold text-muted mb-2"><i class="fa-solid fa-file-pdf me-1"></i> Documento Original: {{ $document->originalFileName }}</h6>
            @if($document->mimeType === 'application/pdf')
                <iframe src="{{ asset('storage/' . $document->filePath) }}" width="100%" height="620px" class="border rounded"></iframe>
            @else
                <img src="{{ asset('storage/' . $document->filePath) }}" class="img-fluid rounded border" alt="Vista previa">
            @endif
        </div>
    </div>

    <!-- Formulario Editable con Datos de la IA -->
    <div class="col-lg-6">
        <div class="card p-4">
            <form method="POST" action="{{ route('documents.confirm', $document) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Codigo del Documento *</label>
                        <input type="text" name="documentCode" class="form-control" value="{{ old('documentCode', $document->documentCode) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tipo de Documento *</label>
                        <select name="documentType" class="form-select" required>
                            @foreach(['Oficio', 'Contrato', 'Memorando', 'Resolucion', 'Solicitud', 'Informe'] as $type)
                                <option value="{{ $type }}" @selected(old('documentType', $document->documentType) === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Fecha de Emision *</label>
                        <input type="date" name="issueDate" class="form-control" value="{{ old('issueDate', optional($document->issueDate)->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Categoria</label>
                        <select name="category_id" class="form-select">
                            <option value="">-- Seleccionar --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('category_id', $document->category_id) == $cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Remitente / Dependencia *</label>
                        <input type="text" name="sender" class="form-control" value="{{ old('sender', $document->sender) }}" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Destinatario *</label>
                        <input type="text" name="recipient" class="form-control" value="{{ old('recipient', $document->recipient) }}" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Asunto *</label>
                        <textarea name="subject" class="form-control" rows="2" required>{{ old('subject', $document->subject) }}</textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Palabras Clave</label>
                        <input type="text" name="keywords" class="form-control" value="{{ old('keywords', $document->keywords) }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Resumen Ejecutivo (IA)</label>
                        <textarea name="summary" class="form-control" rows="2">{{ old('summary', $document->summary) }}</textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-success flex-grow-1 fw-semibold">
                        <i class="fa-solid fa-check-double me-1"></i> Confirmar y Guardar Metadatos
                    </button>
                    <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection