@extends('layouts.app')
@section('title', 'Repositorio Documental')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Repositorio Central de Documentos</h3>
    <a href="{{ route('documents.create') }}" class="btn btn-primary"><i class="fa-solid fa-cloud-arrow-up me-1"></i> Cargar Documento</a>
</div>

<div class="card p-3 mb-4">
    <form method="GET" action="{{ route('documents.index') }}" class="row g-2">
        <div class="col-md-5">
            <input type="text" name="q" class="form-control" placeholder="Buscar por codigo, asunto, remitente o contenido OCR..." value="{{ request('q') }}">
        </div>
        <div class="col-md-3">
            <select name="documentType" class="form-select">
                <option value="">Todos los tipos</option>
                @foreach(['Oficio', 'Contrato', 'Memorando', 'Resolucion', 'Solicitud', 'Informe'] as $t)
                    <option value="{{ $t }}" @selected(request('documentType') === $t)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">Todos los estados</option>
                <option value="validado" @selected(request('status') === 'validado')>Validado</option>
                <option value="pendiente_revision" @selected(request('status') === 'pendiente_revision')>Pendiente</option>
            </select>
        </div>
        <div class="col-md-2 d-grid">
            <button type="submit" class="btn btn-dark"><i class="fa-solid fa-magnifying-glass me-1"></i> Filtrar</button>
        </div>
    </form>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Codigo</th>
                    <th>Tipo</th>
                    <th>Asunto</th>
                    <th>Remitente</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $doc)
                <tr>
                    <td class="fw-bold">{{ $doc->documentCode }}</td>
                    <td>{{ $doc->documentType }}</td>
                    <td>{{ Str::limit($doc->subject, 55) }}</td>
                    <td>{{ $doc->sender }}</td>
                    <td>{{ optional($doc->issueDate)->format('Y-m-d') }}</td>
                    <td>
                        <span class="badge {{ $doc->status === 'validado' ? 'bg-success' : 'bg-warning text-dark' }}">
                            {{ $doc->status === 'validado' ? 'Validado' : 'Por Revisar' }}
                        </span>
                    </td>
                    <td class="d-flex gap-1">
                        <a href="{{ route('documents.show', $doc) }}" class="btn btn-sm btn-outline-primary" title="Ver"><i class="fa-solid fa-eye"></i></a>
                        <a href="{{ route('documents.review', $doc) }}" class="btn btn-sm btn-outline-secondary" title="Editar"><i class="fa-solid fa-pen"></i></a>
                        @if(auth()->user()->isAdmin())
                        <form method="POST" action="{{ route('documents.destroy', $doc) }}" onsubmit="return confirm('¿Eliminar este documento?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No se encontraron documentos con los filtros actuales.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $documents->links() }}
</div>
@endsection