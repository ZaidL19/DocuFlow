<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>DocuFlow AI - Acceso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
<div class="card shadow-sm p-4" style="max-width: 420px; width: 100%; border-radius: 14px;">
    <div class="text-center mb-4">
        <h3 class="fw-bold text-primary"><i class="fa-solid fa-layer-group me-2"></i>DocuFlow AI</h3>
        <p class="text-muted small">Sistema Distribuido de Gestion Documental Inteligente</p>
    </div>
    @if($errors->any())
        <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('login.post') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-semibold">Correo Institucional</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', 'admin@docuflow.com') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Contrasena</label>
            <input type="password" name="password" class="form-control" value="Admin123*" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">Ingresar al Sistema</button>
    </form>
    <div class="mt-3 p-2 bg-light rounded small text-muted text-center">
        <strong>Demo Admin:</strong> admin@docuflow.com / Admin123*<br>
        <strong>Demo Operador:</strong> operador@docuflow.com / Operador123*
    </div>
</div>
</body>
</html>