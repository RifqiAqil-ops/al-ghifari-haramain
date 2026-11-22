<?php

namespace App\Services\Intelligence;

use App\Models\DocumentOcrLog;
use App\Models\Jamaah;
use App\Models\JamaahDocument;
use App\Models\Registration;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\Intelligence\OcrManager;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class DocumentIntelligenceService
{
    protected OcrManager $ocrManager;

    public function __construct(OcrManager $ocrManager)
    {
        $this->ocrManager = $ocrManager;
    }

    /**
     * Run real OCR Analysis on an uploaded document file and stage results for human verification.
     */
    public function analyzeDocument(JamaahDocument $document): DocumentOcrLog
    {
        $documentType = strtolower($document->document_type);
        $disk = Storage::disk('public');
        $absolutePath = $disk->path($document->file_path);

        // Process with Real OCR Provider (Gemini Vision / Tesseract / None)
        $ocrResult = $this->ocrManager->extract($absolutePath, $documentType);
        $extractedData = $ocrResult['data'] ?? [];
        $confidence = (float) ($ocrResult['confidence'] ?? 0.00);
        $isSuccess = $ocrResult['success'] ?? false;

        $validationStatus = $isSuccess ? 'valid' : 'invalid';
        $validationNotes = [];

        if (!$isSuccess) {
            $validationNotes[] = $ocrResult['notes'] ?? 'Gagal membaca dokumen fisik.';
        } else {
            if ($documentType === 'ktp') {
                $nik = $extractedData['nik'] ?? null;
                if (!$nik || strlen($nik) !== 16 || !is_numeric($nik)) {
                    $validationStatus = 'warning';
                    $validationNotes[] = 'Format NIK harus tepat 16 digit numerik.';
                }
            } elseif ($documentType === 'paspor') {
                $passportNo = $extractedData['passport_number'] ?? null;
                $expiry = $extractedData['expiry_date'] ?? null;

                if (!$passportNo || strlen($passportNo) < 7) {
                    $validationStatus = 'warning';
                    $validationNotes[] = 'Nomor paspor tidak sesuai format standar ICAO.';
                }

                if ($expiry) {
                    $expiryDate = Carbon::parse($expiry);
                    if ($expiryDate->isPast() || $expiryDate->diffInMonths(now()) < 6) {
                        $validationStatus = 'invalid';
                        $validationNotes[] = 'Masa berlaku paspor kurang dari 6 bulan atau sudah kadaluarsa (Syarat Visa Ditolak).';
                    }
                }
            }
        }

        return DocumentOcrLog::create([
            'jamaah_id' => $document->jamaah_id,
            'document_id' => $document->id,
            'document_type' => $documentType,
            'extracted_data' => $extractedData,
            'confidence_score' => $confidence,
            'validation_status' => $validationStatus,
            'validation_notes' => implode(' | ', $validationNotes),
            'verification_status' => 'pending_verification',
            'applied_to_jamaah' => false,
        ]);
    }

    /**
     * Human verification step: Staff approves staged OCR data and safely applies it to Jamaah profile.
     */
    public function approveAndApply(DocumentOcrLog $ocrLog, User $user, ?string $verificationNote = null): bool
    {
        $jamaah = $ocrLog->jamaah;
        if (!$jamaah) {
            return false;
        }

        $oldData = $jamaah->only(['name', 'nik', 'passport_number', 'passport_expiry_date', 'birth_place', 'birth_date']);
        $extracted = $ocrLog->extracted_data;

        $updates = [];
        if (!empty($extracted['name'])) {
            $updates['name'] = $extracted['name'];
        }
        if (!empty($extracted['nik']) && strlen($extracted['nik']) === 16) {
            $updates['nik'] = $extracted['nik'];
        }
        if (!empty($extracted['passport_number'])) {
            $updates['passport_number'] = $extracted['passport_number'];
        }
        if (!empty($extracted['expiry_date'])) {
            $updates['passport_expiry_date'] = $extracted['expiry_date'];
        }
        if (!empty($extracted['birth_date'])) {
            $updates['birth_date'] = $extracted['birth_date'];
        }
        if (!empty($extracted['birth_place'])) {
            $updates['birth_place'] = $extracted['birth_place'];
        }

        if (!empty($updates)) {
            $jamaah->update($updates);
        }

        // Also mark original document as diverifikasi if linked
        if ($ocrLog->document) {
            $ocrLog->document->update([
                'status' => 'diverifikasi',
                'verified_at' => now(),
                'verified_by' => $user->id,
                'verification_note' => 'Diverifikasi via Smart Document Intelligence OCR: ' . $verificationNote,
            ]);
        }

        $ocrLog->update([
            'verification_status' => 'approved',
            'verified_by' => $user->id,
            'verified_at' => now(),
            'verification_note' => $verificationNote,
            'applied_to_jamaah' => true,
        ]);

        ActivityLogger::log(
            'OCR_DOCUMENT_VERIFIED',
            'Intelligence',
            "Verifikasi data OCR jamaah {$jamaah->name} (ID: {$jamaah->id}) disetujui oleh {$user->name}.",
            $oldData,
            $jamaah->fresh()->only(array_keys($oldData))
        );

        return true;
    }

    /**
     * Reject staged OCR data.
     */
    public function rejectOcr(DocumentOcrLog $ocrLog, User $user, string $reason): bool
    {
        $ocrLog->update([
            'verification_status' => 'rejected',
            'verified_by' => $user->id,
            'verified_at' => now(),
            'verification_note' => $reason,
            'applied_to_jamaah' => false,
        ]);

        ActivityLogger::log(
            'OCR_DOCUMENT_REJECTED',
            'Intelligence',
            "Hasil OCR dokumen jamaah ID {$ocrLog->jamaah_id} ditolak: {$reason}."
        );

        return true;
    }

    /**
     * Audit incomplete documents across active registered jamaahs.
     */
    public function scanIncompleteDocuments(): array
    {
        $registrations = Registration::with(['jamaah.documents', 'departure', 'package'])
            ->where('status', '!=', 'batal')
            ->get();

        $incomplete = [];

        foreach ($registrations as $reg) {
            $jamaah = $reg->jamaah;
            if (!$jamaah) continue;

            $hasKtp = $jamaah->documents->where('document_type', 'ktp')->where('status', 'diverifikasi')->isNotEmpty();
            $hasPaspor = $jamaah->documents->where('document_type', 'paspor')->where('status', 'diverifikasi')->isNotEmpty();
            $hasFoto = $jamaah->documents->where('document_type', 'pas_foto')->where('status', 'diverifikasi')->isNotEmpty();

            $missing = [];
            if (!$hasKtp) $missing[] = 'KTP';
            if (!$hasPaspor) $missing[] = 'Paspor';
            if (!$hasFoto) $missing[] = 'Pas Foto';

            if (!empty($missing)) {
                $incomplete[] = [
                    'jamaah_id' => $jamaah->id,
                    'jamaah_name' => $jamaah->name,
                    'phone' => $jamaah->phone,
                    'registration_number' => $reg->registration_number,
                    'departure_code' => $reg->departure?->code ?? '-',
                    'departure_date' => $reg->departure?->departure_date ?? '-',
                    'missing_documents' => $missing,
                ];
            }
        }

        return $incomplete;
    }
}
