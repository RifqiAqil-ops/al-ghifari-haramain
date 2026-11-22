<?php

namespace App\Services\Intelligence\Providers;

use App\Services\Intelligence\Contracts\OcrProviderInterface;

class TesseractOcrProvider implements OcrProviderInterface
{
    protected ?string $binaryPath;

    public function __construct()
    {
        $this->binaryPath = config('services.ocr.tesseract_path', env('TESSERACT_PATH'));
    }

    public function isAvailable(): bool
    {
        if (!empty($this->binaryPath) && file_exists($this->binaryPath)) {
            return true;
        }

        // Check if tesseract is in system PATH
        $command = PHP_OS_FAMILY === 'Windows' ? 'where tesseract 2>nul' : 'which tesseract 2>/dev/null';
        $output = @shell_exec($command);

        return !empty(trim($output ?? ''));
    }

    public function getProviderName(): string
    {
        return 'tesseract_local';
    }

    public function extractDocumentData(string $absoluteFilePath, string $documentType): array
    {
        if (!$this->isAvailable()) {
            return [
                'success' => false,
                'data' => [],
                'confidence' => 0.00,
                'is_readable' => false,
                'notes' => 'Tesseract OCR binary tidak ditemukan di server.',
                'provider' => $this->getProviderName(),
            ];
        }

        if (!file_exists($absoluteFilePath)) {
            return [
                'success' => false,
                'data' => [],
                'confidence' => 0.00,
                'is_readable' => false,
                'notes' => 'File fisik dokumen tidak ditemukan.',
                'provider' => $this->getProviderName(),
            ];
        }

        // Tesseract extraction execution
        $cmd = escapeshellcmd($this->binaryPath ?: 'tesseract') . ' ' . escapeshellarg($absoluteFilePath) . ' stdout --psm 6 2>&1';
        $rawOutput = @shell_exec($cmd);

        if (empty(trim($rawOutput ?? ''))) {
            return [
                'success' => false,
                'data' => [],
                'confidence' => 0.00,
                'is_readable' => false,
                'notes' => 'Tesseract tidak menghasilkan teks dari gambar.',
                'provider' => $this->getProviderName(),
            ];
        }

        // Parse regex fields from raw OCR text
        $data = ['raw_text' => $rawOutput];
        if ($documentType === 'ktp') {
            if (preg_match('/\b\d{16}\b/', $rawOutput, $m)) {
                $data['nik'] = $m[0];
            }
        } elseif ($documentType === 'paspor') {
            if (preg_match('/[A-Z][0-9]{7,8}/', $rawOutput, $m)) {
                $data['passport_number'] = $m[0];
            }
        }

        return [
            'success' => true,
            'data' => $data,
            'confidence' => 80.00,
            'is_readable' => true,
            'notes' => 'Teks terbaca via Tesseract Local OCR.',
            'provider' => $this->getProviderName(),
        ];
    }
}
