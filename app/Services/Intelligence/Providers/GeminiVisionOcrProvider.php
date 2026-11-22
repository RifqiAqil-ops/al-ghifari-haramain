<?php

namespace App\Services\Intelligence\Providers;

use App\Services\Intelligence\Contracts\OcrProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiVisionOcrProvider implements OcrProviderInterface
{
    protected ?string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', env('GEMINI_API_KEY'));
        $this->model = config('services.gemini.model', env('GEMINI_MODEL', 'gemini-1.5-flash'));
    }

    public function isAvailable(): bool
    {
        return !empty($this->apiKey);
    }

    public function getProviderName(): string
    {
        return 'gemini_vision';
    }

    public function extractDocumentData(string $absoluteFilePath, string $documentType): array
    {
        if (!$this->isAvailable()) {
            return [
                'success' => false,
                'data' => [],
                'confidence' => 0.00,
                'is_readable' => false,
                'notes' => 'Gemini API Key belum dikonfigurasi di file .env (GEMINI_API_KEY).',
                'provider' => $this->getProviderName(),
            ];
        }

        if (!file_exists($absoluteFilePath)) {
            return [
                'success' => false,
                'data' => [],
                'confidence' => 0.00,
                'is_readable' => false,
                'notes' => 'File fisik dokumen tidak ditemukan di penyimpanan server.',
                'provider' => $this->getProviderName(),
            ];
        }

        $mimeType = mime_content_type($absoluteFilePath) ?: 'image/jpeg';
        $fileBytes = file_get_contents($absoluteFilePath);
        $base64Data = base64_encode($fileBytes);

        $prompt = "Anda adalah engine OCR dokumen resmi untuk verifikasi jamaah Haji & Umrah. " .
            "Tugas Anda: Baca dan ekstrak teks dari gambar dokumen tipe: '{$documentType}'.\n" .
            "Format output yang diwajibkan HANYA berupa JSON murni tanpa markdown, tanpa ```json, dengan struktur persis berikut:\n";

        if ($documentType === 'ktp') {
            $prompt .= '{"document_type": "ktp", "nik": "16 digit angka", "name": "nama lengkap", "birth_place": "tempat lahir", "birth_date": "YYYY-MM-DD", "gender": "L/P", "address": "alamat", "is_readable": true, "confidence": 95.0}';
        } elseif ($documentType === 'paspor') {
            $prompt .= '{"document_type": "paspor", "passport_number": "nomor paspor", "name": "nama lengkap", "nationality": "kewarganegaraan", "birth_date": "YYYY-MM-DD", "expiry_date": "YYYY-MM-DD", "issued_place": "tempat terbit", "is_readable": true, "confidence": 95.0}';
        } else {
            $prompt .= '{"document_type": "' . $documentType . '", "extracted_text": "ringkasan teks", "is_readable": true, "confidence": 85.0}';
        }

        $prompt .= "\nJika gambar buram, terpotong, atau tidak terbaca, set is_readable ke false dan confidence ke 0.0.";

        try {
            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

            $response = Http::timeout(30)->post($endpoint, [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inlineData' => [
                                    'mimeType' => $mimeType,
                                    'data' => $base64Data,
                                ],
                            ],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'responseMimeType' => 'application/json',
                ],
            ]);

            if ($response->successful()) {
                $rawText = $response->json('candidates.0.content.parts.0.text');
                $cleaned = trim(preg_replace('/^```json|```$/m', '', $rawText));
                $parsed = json_decode($cleaned, true);

                if (is_array($parsed)) {
                    $isReadable = $parsed['is_readable'] ?? true;
                    $confidence = (float) ($parsed['confidence'] ?? 85.00);

                    return [
                        'success' => $isReadable,
                        'data' => $parsed,
                        'confidence' => $confidence,
                        'is_readable' => $isReadable,
                        'notes' => $isReadable ? 'Ekstraksi OCR Vision berhasil diproses dari file nyata.' : 'Dokumen fisik buram atau tidak terbaca jelas oleh Vision Engine.',
                        'provider' => $this->getProviderName(),
                    ];
                }
            }

            return [
                'success' => false,
                'data' => [],
                'confidence' => 0.00,
                'is_readable' => false,
                'notes' => 'Vision Engine gagal mem-parsing format dokumen (Status: ' . $response->status() . ').',
                'provider' => $this->getProviderName(),
            ];
        } catch (\Throwable $e) {
            Log::error('Gemini Vision OCR Error: ' . $e->getMessage());
            return [
                'success' => false,
                'data' => [],
                'confidence' => 0.00,
                'is_readable' => false,
                'notes' => 'Gagal menghubungi Vision OCR API: ' . $e->getMessage(),
                'provider' => $this->getProviderName(),
            ];
        }
    }
}
