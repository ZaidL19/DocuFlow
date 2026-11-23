<?php

use App\Models\Document;
use Illuminate\Support\Facades\Route;

Route::get('/v1/documents', function () {
    return response()->json([
        'status' => 'success',
        'count' => Document::where('status', 'validado')->count(),
        'data' => Document::with('category:id,name')
            ->where('status', 'validado')
            ->select('id', 'category_id', 'documentCode', 'documentType', 'subject', 'sender', 'recipient', 'issueDate', 'keywords')
            ->latest()
            ->get(),
    ]);
});