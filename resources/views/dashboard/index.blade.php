@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">Panel de Control Documental</h3>
        <p class="text-muted small mb-0">Metricas operativas y rendimiento</p>
    </div>
    <a href="{{ route('documents.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus me-1"></i> Nuevo Documento
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card p-3 border-start border-4 border-primary">
            <span class="text-muted small">Documentos Registrados</span>
            <h3 class="fw-bold mt-1 mb-0">{{ $totalDocs }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 border-start border-4 border-success">
            <span class="text-muted small">Validados por Usuario</span>
            <h3 class="fw-bold mt-1 mb-0 text-success">{{ $validatedDocs }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 border-start border-4 border-info">
            <span class="text-muted small">Latencia Promedio IA</span>
            <h3 class="fw-bold mt-1 mb-0 text-info">{{ $avgProcessingMs }} ms</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 border-start border-4 border-warning">
            <span class="text-muted small">Exact Match Campos IA</span>
            <h3 class="fw-bold mt-1 mb-0 text-warning">{{ $avgAccuracy }}%</h3>
        </div>
    </div>
</div>

<div class="card p-4">
    <h5 class="fw-bold mb-3">Documentos Recientes</h5>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Codigo</th>
                    <th>Tipo</th>
                    <th>Asunto</th>
                    <th>Remitente</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentDocuments as $doc)
                <tr>
                    <td class="fw-semibold">{{ $doc->documentCode }}</td>
                    <td><span class="badge bg-secondary">{{ $doc->documentType }}</span></td>
                    <td>{{ Str::limit($doc->subject, 65) }}</td>
                    <td>{{ $doc->sender }}</td>
                    <td>
                        @if($doc->status === 'validado')
                            <span class="badge bg-success">Validado</span>
                        @else
                            <span class="badge bg-warning text-dark">Pendiente Revision</span>
                        @endif
                    </td>
                    <td>
                        @if($doc->status === 'pendiente_revision')
                            <a href="{{ route('documents.review', $doc) }}" class="btn btn-sm btn-warning">Revisar IA</a>
                        @else
                            <a href="{{ route('documents.show', $doc) }}" class="btn btn-sm btn-outline-primary">Detalle</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Aun no hay documentos cargados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection