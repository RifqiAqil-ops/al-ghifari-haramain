<?php

namespace App\Services\Intelligence;

use App\Services\Intelligence\Contracts\OcrProviderInterface;
use App\Services\Intelligence\Providers\GeminiVisionOcrProvider;
use App\Services\Intelligence\Providers\TesseractOcrProvider;

class OcrManager
{
    protected GeminiVisionOcrProvider $geminiVisionProvider;
    protected TesseractOcrProvider $tesseractProvider;

    public function __construct()
    {
        $this->geminiVisionProvider = new GeminiVisionOcrProvider();
        $this->tesseractProvider = new TesseractOcrProvider();
    }

    /**
     * Resolve active OCR provider or return null if none available.
     */
    public function getProvider(): ?OcrProviderInterface
    {
        if ($this->geminiVisionProvider->isAvailable()) {
            return $this->geminiVisionProvider;
        }

        if ($this->tesseractProvider->isAvailable()) {
            return $this->tesseractProvider;
        }

        return null;
    }

    /**
     * Process physical file with real OCR engine.
     * Guaranteed: NEVER returns fake/mock data if engine is missing.
     */
    public function extract(string $absoluteFilePath, string $documentType): array
    {
        $provider = $this->getProvider();

        if (!$provider) {
            return [
                'success' => false,
                'data' => [],
                'confidence' => 0.00,
                'is_readable' => false,
                'notes' => 'Layanan OCR tidak tersedia: GEMINI_API_KEY belum dikonfigurasi di .env dan Tesseract OCR binary tidak terpasang di hosting. Silakan atur konfigurasi untuk mengaktifkan pemindaian berkas otomatis.',
                'provider' => 'none',
            ];
        }

        return $provider->extractDocumentData($absoluteFilePath, $documentType);
    }
}
