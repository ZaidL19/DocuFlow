<?php

namespace App\Http\Controllers;

use App\Models\AiExtractionLog;
use App\Models\AuditLog;

class AuditController extends Controller
{
    public function index()
    {
        $auditLogs = AuditLog::with(['user', 'document'])
            ->latest()
            ->paginate(15);

        $aiLogs = AiExtractionLog::with(['user', 'document'])
            ->latest()
            ->take(20)
            ->get();

        return view('audit.index', compact('auditLogs', 'aiLogs'));
    }
}