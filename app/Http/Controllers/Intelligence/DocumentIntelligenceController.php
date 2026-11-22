<?php

namespace App\Http\Controllers\Intelligence;

use App\Http\Controllers\Controller;
use App\Models\DocumentOcrLog;
use App\Models\JamaahDocument;
use App\Services\Intelligence\DocumentIntelligenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DocumentIntelligenceController extends Controller
{
    protected DocumentIntelligenceService $documentService;

    public function __construct(DocumentIntelligenceService $documentService)
    {
        $this->documentService = $documentService;
    }

    public function index(): View
    {
        $pendingOcrLogs = DocumentOcrLog::with(['jamaah', 'document', 'verifier'])
            ->where('verification_status', 'pending_verification')
            ->latest()
            ->paginate(15);

        $verifiedOcrLogs = DocumentOcrLog::with(['jamaah', 'document', 'verifier'])
            ->where('verification_status', '!=', 'pending_verification')
            ->latest()
            ->take(20)
            ->get();

        $incompleteDocuments = $this->documentService->scanIncompleteDocuments();
        $ocrProvider = app(\App\Services\Intelligence\OcrManager::class)->getProvider()?->getProviderName() ?? 'none';

        return view('intelligence.documents', compact('pendingOcrLogs', 'verifiedOcrLogs', 'incompleteDocuments', 'ocrProvider'));
    }

    public function analyze(JamaahDocument $document): RedirectResponse
    {
        $ocrLog = $this->documentService->analyzeDocument($document);

        return back()->with('toast_success', 'Dokumen berhasil dianalisis OCR. Silakan periksa hasil ekstraksi sebelum menerapkan perubahan.');
    }

    public function approve(Request $request, DocumentOcrLog $log): RedirectResponse
    {
        $user = Auth::user();
        $note = $request->input('note', 'Diverifikasi oleh staf via OCR Review');

        $this->documentService->approveAndApply($log, $user, $note);

        return back()->with('toast_success', 'Data dokumen berhasil diverifikasi dan diterapkan ke profil master jamaah.');
    }

    public function reject(Request $request, DocumentOcrLog $log): RedirectResponse
    {
        $request->validate(['reason' => 'required|string|max:255']);
        $user = Auth::user();

        $this->documentService->rejectOcr($log, $user, $request->reason);

        return back()->with('toast_warning', 'Hasil ekstraksi OCR telah ditolak.');
    }
}
