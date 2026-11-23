<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'documentCode',
        'documentType',
        'subject',
        'sender',
        'recipient',
        'issueDate',
        'keywords',
        'summary',
        'extractedText',
        'filePath',
        'originalFileName',
        'mimeType',
        'fileSize',
        'status',
    ];

    protected $casts = [
        'issueDate' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'category_id');
    }

    public function aiLogs(): HasMany
    {
        return $this->hasMany(AiExtractionLog::class, 'document_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'document_id');
    }
}