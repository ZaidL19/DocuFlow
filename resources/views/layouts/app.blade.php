<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DocuFlow AI') - Gestion Documental Inteligente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        html, body {
            height: 100vh;
            overflow: hidden;
            background-color: #f8fafc;
            font-family: 'Segoe UI', system-ui, sans-serif;
            margin: 0;
        }
        .app-shell {
            display: flex;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
        }
        .sidebar {
            width: 245px;
            flex-shrink: 0;
            height: 100vh;
            background: #0f172a;
            color: #e2e8f0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 1.25rem 1rem;
        }
        .sidebar .nav-link {
            color: #94a3b8;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 4px;
            font-weight: 500;
            transition: all 0.15s ease;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: #2563eb;
            color: #fff;
        }
        .main-content {
            flex-grow: 1;
            height: 100vh;
            overflow-y: auto;
            padding: 1.5rem 2rem;
            display: flex;
            flex-direction: column;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        .table th, .table td {
            padding-top: 0.55rem;
            padding-bottom: 0.55rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div>
            <div class="d-flex align-items-center mb-4 px-2">
                <i class="fa-solid fa-layer-group text-primary fs-4 me-2"></i>
                <span class="fs-5 fw-bold text-white">DocuFlow</span>
            </div>
            <nav class="nav flex-column">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-pie me-2"></i> Dashboard
                </a>
                <a href="{{ route('documents.create') }}" class="nav-link {{ request()->routeIs('documents.create') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-circle-plus me-2"></i> Registrar Documento
                </a>
                <a href="{{ route('documents.index') }}" class="nav-link {{ request()->routeIs('documents.index', 'documents.review', 'documents.show') ? 'active' : '' }}">
                    <i class="fa-solid fa-folder-open me-2"></i> Repositorio
                </a>
                <a href="{{ route('rag.index') }}" class="nav-link {{ request()->routeIs('rag.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-robot me-2"></i> Asistente RAG
                </a>
                <a href="{{ route('audit.index') }}" class="nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-shield-halved me-2"></i> Auditoria
                </a>
            </nav>
        </div>

        <div class="border-top border-secondary pt-3">
            <div class="px-2 mb-2 small">
                <div class="text-white fw-semibold">{{ auth()->user()->name }}</div>
                <span class="badge bg-primary">{{ auth()->user()->role }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                    <i class="fa-solid fa-right-from-bracket me-1"></i> Cerrar Sesion
                </button>
            </form>
        </div>
    </aside>

    <main class="main-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show py-2 mb-3" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger py-2 mb-3">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>