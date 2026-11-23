<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiExtractionLog extends Model
{
    protected $fillable = [
        'document_id',
        'user_id',
        'modelUsed',
        'extractionMethod',
        'rawAiResponse',
        'humanVerifiedData',
        'fieldMatchAccuracy',
        'processingTimeMs',
    ];

    protected $casts = [
        'rawAiResponse' => 'array',
        'humanVerifiedData' => 'array',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}