@extends('layouts.app')
@section('title', 'Asistente RAG')
@section('content')
<h3 class="fw-bold mb-1">Asistente Conversacional RAG</h3>
<p class="text-muted small mb-4">Consulta informacion contenida en tus documentos validados con citas de fuentes verificables.</p>

<div class="card p-4 mb-4">
    <div class="input-group">
        <input type="text" id="ragQuestion" class="form-control form-control-lg" placeholder="Ej. ¿Que documentos fueron emitidos por Recursos Humanos o sobre seguridad?">
        <button id="btnAskRag" class="btn btn-primary px-4 fw-semibold">
            <i class="fa-solid fa-paper-plane me-1"></i> Consultar IA
        </button>
    </div>
</div>

<div id="ragResultBox" class="card p-4 d-none">
    <h6 class="fw-bold text-primary mb-2"><i class="fa-solid fa-robot me-1"></i> Respuesta Fundamentada:</h6>
    <div id="ragAnswerText" class="mb-3" style="white-space: pre-line;"></div>
    <hr>
    <h6 class="fw-bold small text-muted">Fuentes Documentales Recuperadas:</h6>
    <ul id="ragSourcesList" class="list-group list-group-flush small"></ul>
</div>

@push('scripts')
<script>
document.getElementById('btnAskRag').addEventListener('click', async () => {
    const question = document.getElementById('ragQuestion').value.trim();
    if (!question) return;

    const btn = document.getElementById('btnAskRag');
    const box = document.getElementById('ragResultBox');
    const answerEl = document.getElementById('ragAnswerText');
    const sourcesEl = document.getElementById('ragSourcesList');

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Analizando...';
    box.classList.remove('d-none');
    answerEl.textContent = 'Recuperando documentos relevantes y generando respuesta con Gemini...';
    sourcesEl.innerHTML = '';

    try {
        const res = await fetch("{{ route('rag.query') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ question })
        });
        const data = await res.json();
        if (!data.ok) {
            answerEl.innerHTML = `<span class="text-danger">${data.message}</span>`;
        } else {
            answerEl.textContent = data.answer;
            data.sources.forEach(s => {
                const li = document.createElement('li');
                li.className = 'list-group-item px-0';
                li.innerHTML = `<strong>[${s.documentCode}]</strong> (${s.documentType} - ${s.issueDate}): ${s.subject}`;
                sourcesEl.appendChild(li);
            });
        }
    } catch (err) {
        answerEl.innerHTML = '<span class="text-danger">Error de red al consultar el asistente.</span>';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> Consultar IA';
    }
});
</script>
@endpush
@endsection