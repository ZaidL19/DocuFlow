@extends('layouts.app')
@section('title', 'Detalle del Documento')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-bold mb-0">{{ $document->documentCode }} - {{ $document->documentType }}</h3>
    <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">Volver</a>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card p-4 mb-3">
            <h5 class="fw-bold mb-3">Metadatos Validados</h5>
            <dl class="row mb-0">
                <dt class="col-sm-4">Asunto:</dt><dd class="col-sm-8">{{ $document->subject }}</dd>
                <dt class="col-sm-4">Remitente:</dt><dd class="col-sm-8">{{ $document->sender }}</dd>
                <dt class="col-sm-4">Destinatario:</dt><dd class="col-sm-8">{{ $document->recipient }}</dd>
                <dt class="col-sm-4">Fecha Emision:</dt><dd class="col-sm-8">{{ optional($document->issueDate)->format('Y-m-d') }}</dd>
                <dt class="col-sm-4">Palabras Clave:</dt><dd class="col-sm-8">{{ $document->keywords }}</dd>
                <dt class="col-sm-4">Resumen:</dt><dd class="col-sm-8">{{ $document->summary }}</dd>
            </dl>
        </div>

        @if($document->aiLogs->isNotEmpty())
        <div class="card p-4">
            <h6 class="fw-bold text-primary">Metricas de Extraccion</h6>
            @foreach($document->aiLogs as $log)
                <div class="small text-muted">
                    Modelo: <strong>{{ $log->modelUsed }}</strong> |
                    Tiempo: <strong>{{ $log->processingTimeMs }} ms</strong> |
                    Exact Match: <strong>{{ $log->fieldMatchAccuracy ?? 'Pendiente' }}%</strong>
                </div>
            @endforeach
        </div>
        @endif
    </div>
    <div class="col-lg-6">
        <div class="card p-3">
            <iframe src="{{ asset('storage/' . $document->filePath) }}" width="100%" height="550px" class="border rounded"></iframe>
        </div>
    </div>
</div>
@endsection