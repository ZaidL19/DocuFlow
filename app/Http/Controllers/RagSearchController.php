<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Document;
use App\Services\AiDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RagSearchController extends Controller
{
    public function index()
    {
        return view('rag.index');
    }

    public function query(Request $request, AiDocumentService $aiService)
    {
        $request->validate([
            'question' => ['required', 'string', 'min:4', 'max:500'],
        ]);

        $question = $request->input('question');

        // Recuperacion de fragmentos relevantes desde documentos validados
        $terms = array_filter(explode(' ', preg_replace('/[^a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s]/u', '', $question)), fn($w) => mb_strlen($w) > 3);

        $docsQuery = Document::where('status', 'validado');
        if (!empty($terms)) {
            $docsQuery->where(function ($q) use ($terms) {
                foreach ($terms as $term) {
                    $q->orWhere('subject', 'like', "%{$term}%")
                      ->orWhere('sender', 'like', "%{$term}%")
                      ->orWhere('keywords', 'like', "%{$term}%")
                      ->orWhere('extractedText', 'like', "%{$term}%");
                }
            });
        }

        $retrieved = $docsQuery->latest()->take(5)->get()->toArray();

        try {
            $ragResult = $aiService->answerRagQuery($question, $retrieved);

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'RAG_QUERY',
                'details' => "Consulta RAG: '{$question}' (" . count($retrieved) . " fuentes recuperadas).",
                'ipAddress' => $request->ip(),
            ]);

            return response()->json([
                'ok' => true,
                'answer' => $ragResult['answer'],
                'sources' => array_map(fn($d) => [
                    'id' => $d['id'],
                    'documentCode' => $d['documentCode'],
                    'documentType' => $d['documentType'],
                    'subject' => $d['subject'],
                    'issueDate' => substr((string) $d['issueDate'], 0, 10),
                ], $ragResult['sources']),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}