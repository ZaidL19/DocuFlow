<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\RagSearchController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Modulo de Gestion Documental + IA + Carga Manual
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/create', [DocumentController::class, 'create'])->name('documents.create');
    Route::post('/documents/process-ai', [DocumentController::class, 'processWithAi'])->name('documents.processAi');
    Route::post('/documents/manual', [DocumentController::class, 'storeManual'])->name('documents.storeManual');
    Route::get('/documents/{document}/review', [DocumentController::class, 'review'])->name('documents.review');
    Route::put('/documents/{document}/confirm', [DocumentController::class, 'confirmReview'])->name('documents.confirm');
    Route::get('/documents/{document}', [DocumentController::class, 'show'])->name('documents.show');

    // Solo Administrador puede eliminar documentos
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])
        ->middleware('role:Administrador')
        ->name('documents.destroy');

    // Modulo Busqueda Semantica & Asistente RAG
    Route::get('/rag-assistant', [RagSearchController::class, 'index'])->name('rag.index');
    Route::post('/rag-assistant/query', [RagSearchController::class, 'query'])->name('rag.query');

    // Modulo Auditoria y Trazabilidad Experimental
    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
});