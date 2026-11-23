@extends('layouts.app')
@section('title', 'Auditoria y Trazabilidad IA')
@section('content')
<h3 class="fw-bold mb-4">Auditoria del Sistema y Registro Experimental IA</h3>

<div class="card p-4 mb-4">
    <h5 class="fw-bold text-primary mb-3">Trazas Experimentales de Extraccion IA (Semana 11 / 16)</h5>
    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Documento</th>
                    <th>Modelo IA</th>
                    <th>Latencia (ms)</th>
                    <th>Exact Match (%)</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                @forelse($aiLogs as $log)
                <tr>
                    <td>{{ $log->id }}</td>
                    <td>{{ $log->document->documentCode ?? 'N/A' }}</td>
                    <td><code>{{ $log->modelUsed }}</code></td>
                    <td>{{ $log->processingTimeMs }} ms</td>
                    <td>
                        @if(!is_null($log->fieldMatchAccuracy))
                            <span class="badge bg-success">{{ $log->fieldMatchAccuracy }}%</span>
                        @else
                            <span class="badge bg-secondary">Sin validar</span>
                        @endif
                    </td>
                    <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted">No hay registros de extraccion IA aun.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card p-4">
    <h5 class="fw-bold mb-3">Historial de Auditoria General</h5>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Accion</th>
                    <th>Documento</th>
                    <th>Detalles</th>
                    <th>IP</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                @foreach($auditLogs as $audit)
                <tr>
                    <td>{{ $audit->user->name ?? 'Sistema' }}</td>
                    <td><span class="badge bg-dark">{{ $audit->action }}</span></td>
                    <td>{{ $audit->document->documentCode ?? 'N/A' }}</td>
                    <td>{{ $audit->details }}</td>
                    <td><code>{{ $audit->ipAddress }}</code></td>
                    <td>{{ $audit->created_at->format('Y-m-d H:i:s') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $auditLogs->links() }}
</div>
@endsection