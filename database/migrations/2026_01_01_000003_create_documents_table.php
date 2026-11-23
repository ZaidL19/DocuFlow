<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('category_id')->nullable()->constrained('document_categories')->onDelete('set null');
            $table->string('documentCode', 80);
            $table->string('documentType', 60); // Oficio, Contrato, Memorando, Resolucion, Solicitud, Informe
            $table->text('subject');
            $table->string('sender', 180);
            $table->string('recipient', 180);
            $table->date('issueDate');
            $table->text('keywords')->nullable();
            $table->text('summary')->nullable();
            $table->longText('extractedText')->nullable();
            $table->string('filePath', 255);
            $table->string('originalFileName', 255);
            $table->string('mimeType', 100)->nullable();
            $table->unsignedBigInteger('fileSize')->default(0);
            $table->enum('status', ['pendiente_revision', 'validado', 'archivado'])->default('pendiente_revision');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};