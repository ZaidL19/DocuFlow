<?php

namespace App\Http\Controllers;

use App\Models\AiExtractionLog;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Services\AiDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::with(['user', 'category'])->latest();

        if ($request->filled('q')) {
            $term = $request->input('q');
            $query->where(function ($q) use ($term) {
                $q->where('documentCode', 'like', "%{$term}%")
                  ->orWhere('subject', 'like', "%{$term}%")
                  ->orWhere('sender', 'like', "%{$term}%")
                  ->orWhere('keywords', 'like', "%{$term}%")
                  ->orWhere('extractedText', 'like', "%{$term}%");
            });
        }

        if ($request->filled('documentType')) {
            $query->where('documentType', $request->input('documentType'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $documents = $query->paginate(10)->withQueryString();
        $categories = DocumentCategory::all();

        return view('documents.index', compact('documents', 'categories'));
    }

    public function create()
    {
        $categories = DocumentCategory::all();
        return view('documents.create', compact('categories'));
    }

    /**
     * Paso 1: Sube el documento, ejecuta OCR/IA real y redirige a la pantalla de revision editable.
     */
    public function processWithAi(Request $request, AiDocumentService $aiService)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'category_id' => ['nullable', 'exists:document_categories,id'],
        ]);

        $file = $request->file('file');
        $storedPath = $file->store('documents', 'public');
        $absolutePath = Storage::disk('public')->path($storedPath);

        try {
            $aiResult = $aiService->extractMetadata($absolutePath, $file->getMimeType());
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($storedPath);
            return back()->withErrors(['file' => 'Error al procesar con IA: ' . $e->getMessage()]);
        }

        $extracted = $aiResult['data'];

        // Crear documento en estado pendiente_revision
        $document = Document::create([
            'user_id' => Auth::id(),
            'category_id' => $request->input('category_id'),
            'documentCode' => $extracted['documentCode'],
            'documentType' => $extracted['documentType'],
            'subject' => $extracted['subject'],
            'sender' => $extracted['sender'],
            'recipient' => $extracted['recipient'],
            'issueDate' => $extracted['issueDate'],
            'keywords' => $extracted['keywords'],
            'summary' => $extracted['summary'],
            'extractedText' => $aiResult['extractedText'],
            'filePath' => $storedPath,
            'originalFileName' => $file->getClientOriginalName(),
            'mimeType' => $file->getMimeType(),
            'fileSize' => $file->getSize(),
            'status' => 'pendiente_revision',
        ]);

        // Registrar traza experimental en ai_extraction_logs
        AiExtractionLog::create([
            'document_id' => $document->id,
            'user_id' => Auth::id(),
            'modelUsed' => $aiResult['modelUsed'],
            'extractionMethod' => 'multimodal_api',
            'rawAiResponse' => $aiResult['rawPayload'],
            'processingTimeMs' => $aiResult['processingTimeMs'],
        ]);

        AuditLog::create([
            'document_id' => $document->id,
            'user_id' => Auth::id(),
            'action' => 'AI_EXTRACT',
            'details' => "Extraccion ejecutada con {$aiResult['modelUsed']} en {$aiResult['processingTimeMs']} ms.",
            'ipAddress' => $request->ip(),
        ]);

        return redirect()->route('documents.review', $document->id)
            ->with('success', "Metadatos extraidos por IA en {$aiResult['processingTimeMs']} ms. Revisa y confirma los campos.");
    }

    /**
     * Paso 2: Vista de revision lado a lado (visor PDF/Imagen + formulario editable).
     */
    public function review(Document $document)
    {
        $categories = DocumentCategory::all();
        $latestAiLog = $document->aiLogs()->latest()->first();

        return view('documents.review', compact('document', 'categories', 'latestAiLog'));
    }
    /**
     * Carga Manual: Guarda el archivo y los metadatos ingresados directamente por el operador.
     */
    public function storeManual(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'category_id' => ['nullable', 'exists:document_categories,id'],
            'documentCode' => ['required', 'string', 'max:80'],
            'documentType' => ['required', 'string', 'max:60'],
            'subject' => ['required', 'string'],
            'sender' => ['required', 'string', 'max:180'],
            'recipient' => ['required', 'string', 'max:180'],
            'issueDate' => ['required', 'date'],
            'keywords' => ['nullable', 'string'],
            'summary' => ['nullable', 'string'],
        ]);

        $file = $request->file('file');
        $storedPath = $file->store('documents', 'public');
        $absolutePath = Storage::disk('public')->path($storedPath);

        // Extraer texto plano local si es PDF para mantener disponible la busqueda y el RAG
        $extractedText = '';
        if ($file->getMimeType() === 'application/pdf' && class_exists(\Smalot\PdfParser\Parser::class)) {
            try {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($absolutePath);
                $extractedText = trim($pdf->getText());
            } catch (\Throwable $e) {
                $extractedText = '';
            }
        }

        $document = Document::create([
            'user_id' => Auth::id(),
            'category_id' => $validated['category_id'] ?? null,
            'documentCode' => $validated['documentCode'],
            'documentType' => $validated['documentType'],
            'subject' => $validated['subject'],
            'sender' => $validated['sender'],
            'recipient' => $validated['recipient'],
            'issueDate' => $validated['issueDate'],
            'keywords' => $validated['keywords'] ?? null,
            'summary' => $validated['summary'] ?? null,
            'extractedText' => $extractedText,
            'filePath' => $storedPath,
            'originalFileName' => $file->getClientOriginalName(),
            'mimeType' => $file->getMimeType(),
            'fileSize' => $file->getSize(),
            'status' => 'validado',
        ]);

        AuditLog::create([
            'document_id' => $document->id,
            'user_id' => Auth::id(),
            'action' => 'MANUAL_UPLOAD',
            'details' => "Registro documental manual ingresado directamente ({$document->documentCode}).",
            'ipAddress' => $request->ip(),
        ]);

        return redirect()->route('documents.index')
            ->with('success', "Documento {$document->documentCode} registrado manualmente correctamente.");
    }

    /**
     * Paso 3: Confirma los metadatos corregidos por el usuario y calcula Exact Match experimental.
     */
    public function confirmReview(Request $request, Document $document)
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'exists:document_categories,id'],
            'documentCode' => ['required', 'string', 'max:80'],
            'documentType' => ['required', 'string', 'max:60'],
            'subject' => ['required', 'string'],
            'sender' => ['required', 'string', 'max:180'],
            'recipient' => ['required', 'string', 'max:180'],
            'issueDate' => ['required', 'date'],
            'keywords' => ['nullable', 'string'],
            'summary' => ['nullable', 'string'],
        ]);

        $document->update(array_merge($validated, ['status' => 'validado']));

        // Calcular precision Exact Match comparando salida cruda de IA vs validacion humana
        $aiLog = $document->aiLogs()->latest()->first();
        if ($aiLog && is_array($aiLog->rawAiResponse)) {
            $fieldsToCompare = ['documentCode', 'documentType', 'subject', 'sender', 'recipient', 'issueDate'];
            $matches = 0;
            foreach ($fieldsToCompare as $field) {
                $aiVal = mb_strtolower(trim((string) ($aiLog->rawAiResponse[$field] ?? '')));
                $humanVal = mb_strtolower(trim((string) ($validated[$field] ?? '')));
                if ($aiVal === $humanVal) {
                    $matches++;
                }
            }
            $accuracy = round(($matches / count($fieldsToCompare)) * 100, 2);

            $aiLog->update([
                'humanVerifiedData' => $validated,
                'fieldMatchAccuracy' => $accuracy,
            ]);
        }

        AuditLog::create([
            'document_id' => $document->id,
            'user_id' => Auth::id(),
            'action' => 'VALIDATE_METADATA',
            'details' => "Metadatos confirmados y almacenados ({$document->documentCode}).",
            'ipAddress' => $request->ip(),
        ]);

        return redirect()->route('documents.index')
            ->with('success', "Documento {$document->documentCode} validado y registrado correctamente.");
    }

    public function show(Document $document)
    {
        $document->load(['user', 'category', 'aiLogs', 'auditLogs.user']);
        return view('documents.show', compact('document'));
    }

    public function destroy(Request $request, Document $document)
    {
        $code = $document->documentCode;
        if ($document->filePath && Storage::disk('public')->exists($document->filePath)) {
            Storage::disk('public')->delete($document->filePath);
        }

        AuditLog::create([
            'document_id' => null,
            'user_id' => Auth::id(),
            'action' => 'DELETE_DOCUMENT',
            'details' => "Eliminacion definitiva del documento {$code}.",
            'ipAddress' => $request->ip(),
        ]);

        $document->delete();

        return redirect()->route('documents.index')
            ->with('success', "Documento {$code} eliminado.");
    }
}