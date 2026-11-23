<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

class AiDocumentService
{
    protected string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = (string) config('services.gemini.api_key');
        $this->model = (string) config('services.gemini.model', 'gemini-3.5-flash-lite');
    }

    /**
     * Extrae metadatos estructurados de un documento PDF o Imagen usando Google Gemini API.
     */
    public function extractMetadata(string $absoluteFilePath, string $mimeType): array
    {
        $startTime = microtime(true);
        $rawText = '';

        // 1. Extraer texto plano local si es PDF (util para busqueda hibrida / RAG)
        if ($mimeType === 'application/pdf' && class_exists(Parser::class)) {
            try {
                $parser = new Parser();
                $pdf = $parser->parseFile($absoluteFilePath);
                $rawText = trim($pdf->getText());
            } catch (\Throwable $e) {
                Log::warning('PDFParser fallback: ' . $e->getMessage());
            }
        }

        // 2. Validar existencia de API Key real
        if (empty($this->apiKey)) {
            throw new \RuntimeException('No se ha configurado GEMINI_API_KEY en el archivo .env. Configura tu clave API para ejecutar la extraccion real con IA.');
        }

        $fileBytes = file_get_contents($absoluteFilePath);
        $base64Data = base64_encode($fileBytes);

        $prompt = <<<PROMPT
Eres un especialista en gestion documental administrativa del sector publico y privado en Peru.
Analiza el documento adjunto (mediante OCR visual y comprension semantica) y devuelve UNICAMENTE un objeto JSON valido sin bloques de markdown ni texto adicional, con exactamente estas claves:
{
  "documentCode": "codigo o numero oficial del documento (ej. OFICIO N° 015-2026-UNS), o 'S/N' si no figura",
  "documentType": "clasificar estrictamente en uno de: Oficio, Contrato, Memorando, Resolucion, Solicitud, Informe",
  "subject": "asunto principal o sumilla del documento",
  "sender": "institucion, oficina o persona remitente",
  "recipient": "institucion, oficina o persona destinataria",
  "issueDate": "fecha de emision en formato YYYY-MM-DD (si no hay fecha explicita, usar la fecha actual)",
  "keywords": "5 palabras clave separadas por comas",
  "summary": "resumen ejecutivo de 2 lineas del contenido del documento",
  "fullOcrText": "transcripcion limpia del texto principal del documento"
}
PROMPT;

        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        $response = Http::withOptions([
            'force_ip_resolve' => 'v4',
            'verify' => false,
            'connect_timeout' => 15,
        ])->timeout(60)->post($endpoint, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data' => $base64Data,
                            ],
                        ],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'responseMimeType' => 'application/json',
            ],
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('Error en API Gemini (' . $response->status() . '): ' . $response->body());
        }

        $payload = $response->json();
        $jsonText = $payload['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
        $parsed = json_decode($jsonText, true);

        if (!is_array($parsed)) {
            throw new \RuntimeException('La respuesta de la IA no tuvo un formato JSON valido.');
        }

        $elapsedMs = (int) round((microtime(true) - $startTime) * 1000);

        return [
            'modelUsed' => $this->model,
            'processingTimeMs' => $elapsedMs,
            'extractedText' => !empty($parsed['fullOcrText']) ? $parsed['fullOcrText'] : $rawText,
            'data' => [
                'documentCode' => $parsed['documentCode'] ?? 'S/N',
                'documentType' => $parsed['documentType'] ?? 'Oficio',
                'subject' => $parsed['subject'] ?? '',
                'sender' => $parsed['sender'] ?? '',
                'recipient' => $parsed['recipient'] ?? '',
                'issueDate' => $parsed['issueDate'] ?? date('Y-m-d'),
                'keywords' => $parsed['keywords'] ?? '',
                'summary' => $parsed['summary'] ?? '',
            ],
            'rawPayload' => $parsed,
        ];
    }

    /**
     * Asistente RAG fundamentado exclusivamente en los documentos recuperados de MySQL.
     */
    public function answerRagQuery(string $question, array $retrievedDocs): array
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('Configura GEMINI_API_KEY en .env para habilitar el asistente RAG.');
        }

        if (empty($retrievedDocs)) {
            return [
                'answer' => 'No se encontraron documentos relevantes en el repositorio institucional para responder a tu consulta.',
                'sources' => [],
            ];
        }

        $contextBlocks = [];
        foreach ($retrievedDocs as $idx => $doc) {
            $num = $idx + 1;
            $snippet = mb_substr((string) ($doc['extractedText'] ?: $doc['summary'] ?: $doc['subject']), 0, 1800);
            $contextBlocks[] = "[FUENTE {$num}] Codigo: {$doc['documentCode']} | Tipo: {$doc['documentType']} | Fecha: {$doc['issueDate']} | Remitente: {$doc['sender']} | Asunto: {$doc['subject']}\nContenido: {$snippet}";
        }

        $contextString = implode("\n\n---\n\n", $contextBlocks);

        $prompt = <<<PROMPT
Eres el asistente documental inteligente de DocuFlow AI.
Responde la pregunta del usuario basandote UNICAMENTE en las siguientes fuentes documentales recuperadas.
Reglas obligatorias:
1. Cita explicitamente el codigo del documento fuente entre corchetes (ej. [OFICIO N° 015-2026-UNS]) en cada afirmacion.
2. Si la informacion no esta en las fuentes proporcionadas, indica claramente que no consta en los documentos recuperados. No inventes datos.

PREGUNTA DEL USUARIO:
{$question}

DOCUMENTOS RECUPERADOS:
{$contextString}
PROMPT;

        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        $response = Http::withOptions([
            'force_ip_resolve' => 'v4',
            'verify' => false,
            'connect_timeout' => 15,
        ])->timeout(60)->post($endpoint, [
            'contents' => [
                [
                    'parts' => [['text' => $prompt]],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.2,
            ],
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('Error al consultar asistente RAG: ' . $response->body());
        }

        $answerText = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? 'No se pudo generar respuesta.';

        return [
            'answer' => $answerText,
            'sources' => $retrievedDocs,
        ];
    }
}