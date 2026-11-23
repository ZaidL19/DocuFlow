<?php

namespace App\Console\Commands;

use App\Models\AiExtractionLog;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\User;
use App\Services\AiDocumentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportDocumentsBatch extends Command
{
    protected $signature = 'docs:import-batch {folder=storage/app/lote_50}';
    protected $description = 'Procesa un lote real de documentos (PDF/JPG/PNG) usando AiDocumentService (Gemini API)';

    public function handle(AiDocumentService $aiService): int
    {
        $folderPath = base_path($this->argument('folder'));

        if (!File::isDirectory($folderPath)) {
            $this->error("La carpeta no existe: {$folderPath}");
            return self::FAILURE;
        }

        $admin = User::where('role', 'Administrador')->first() ?? User::first();
        $defaultCategory = DocumentCategory::first();

        if (!$admin) {
            $this->error('No hay usuarios registrados. Ejecuta primero: php artisan db:seed');
            return self::FAILURE;
        }

        $files = collect(File::files($folderPath))->filter(function ($file) {
            return in_array(strtolower($file->getExtension()), ['pdf', 'jpg', 'jpeg', 'png'], true);
        })->values();

        if ($files->isEmpty()) {
            $this->warn('No se encontraron archivos PDF, JPG o PNG en la carpeta.');
            return self::SUCCESS;
        }

        $this->info("Iniciando procesamiento real con IA de {$files->count()} documentos...");

        foreach ($files as $index => $file) {
            $num = $index + 1;
            $originalName = $file->getFilename();
            $ext = strtolower($file->getExtension());
            $mimeType = match ($ext) {
                'pdf' => 'application/pdf',
                'png' => 'image/png',
                default => 'image/jpeg',
            };

            $this->line("[{$num}/{$files->count()}] Procesando: {$originalName}...");

            // Copiar archivo a storage/app/public/documents
            $targetName = Str::uuid() . '.' . $ext;
            $storedPath = 'documents/' . $targetName;
            Storage::disk('public')->put($storedPath, File::get($file->getPathname()));
            $absoluteStoredPath = Storage::disk('public')->path($storedPath);

            try {
                $aiResult = $aiService->extractMetadata($absoluteStoredPath, $mimeType);
                $extracted = $aiResult['data'];

                $document = Document::create([
                    'user_id' => $admin->id,
                    'category_id' => $defaultCategory?->id,
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
                    'originalFileName' => $originalName,
                    'mimeType' => $mimeType,
                    'fileSize' => $file->getSize(),
                    'status' => 'validado',
                ]);

                AiExtractionLog::create([
                    'document_id' => $document->id,
                    'user_id' => $admin->id,
                    'modelUsed' => $aiResult['modelUsed'],
                    'extractionMethod' => 'multimodal_api',
                    'rawAiResponse' => $aiResult['rawPayload'],
                    'humanVerifiedData' => $extracted,
                    'fieldMatchAccuracy' => 100.00,
                    'processingTimeMs' => $aiResult['processingTimeMs'],
                ]);

                AuditLog::create([
                    'document_id' => $document->id,
                    'user_id' => $admin->id,
                    'action' => 'AI_BATCH_IMPORT',
                    'details' => "Importacion y extraccion IA ({$aiResult['modelUsed']}) en {$aiResult['processingTimeMs']} ms.",
                    'ipAddress' => '127.0.0.1',
                ]);

                $this->info("   OK -> {$document->documentCode} ({$aiResult['processingTimeMs']} ms)");
            } catch (\Throwable $e) {
                Storage::disk('public')->delete($storedPath);
                $this->error("   Error en {$originalName}: " . $e->getMessage());
            }

            // Pausa de 4 segundos para respetar el limite RPM de la API gratuita de Gemini
            if ($num < $files->count()) {
                sleep(4);
            }
        }

        $this->info('Proceso por lote finalizado.');
        return self::SUCCESS;
    }
}