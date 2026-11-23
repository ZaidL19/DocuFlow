<?php

namespace App\Http\Controllers;

use App\Models\AiExtractionLog;
use App\Models\Document;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalDocs = Document::count();
        $validatedDocs = Document::where('status', 'validado')->count();
        $avgProcessingMs = (int) round(AiExtractionLog::avg('processingTimeMs') ?? 0);
        $avgAccuracy = round(AiExtractionLog::whereNotNull('fieldMatchAccuracy')->avg('fieldMatchAccuracy') ?? 0, 1);

        $docsByType = Document::select('documentType', DB::raw('count(*) as total'))
            ->groupBy('documentType')
            ->get();

        $recentDocuments = Document::with(['user', 'category'])
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'totalDocs',
            'validatedDocs',
            'avgProcessingMs',
            'avgAccuracy',
            'docsByType',
            'recentDocuments'
        ));
    }
}