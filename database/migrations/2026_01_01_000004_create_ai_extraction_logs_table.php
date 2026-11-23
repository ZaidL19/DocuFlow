<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_extraction_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('modelUsed', 80);
            $table->string('extractionMethod', 40)->default('multimodal_api'); // multimodal_api | ocr_fallback
            $table->json('rawAiResponse');
            $table->json('humanVerifiedData')->nullable();
            $table->decimal('fieldMatchAccuracy', 5, 2)->nullable();
            $table->integer('processingTimeMs')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_extraction_logs');
    }
};