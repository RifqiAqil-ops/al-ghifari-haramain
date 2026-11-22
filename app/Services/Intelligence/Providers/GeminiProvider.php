<?php

namespace App\Services\Intelligence\Providers;

use App\Services\Intelligence\Contracts\AiProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProvider implements AiProviderInterface
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
        return 'gemini';
    }

    public function chat(string $prompt, array $context = [], array $history = []): array
    {
        if (!$this->isAvailable()) {
            throw new \RuntimeException('Gemini API Key is not configured.');
        }

        $systemInstruction = "Anda adalah asisten AI eksekutif untuk Al Ghifari Haramain, biro perjalanan Haji & Umrah. " .
            "Berikan jawaban profesional, akurat, santun, dan berdasarkan data yang diberikan. Jangan berasumsi data di luar konteks.";

        $contents = [];
        foreach ($history as $msg) {
            $contents[] = [
                'role' => $msg['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $msg['content']]],
            ];
        }

        $groundedPrompt = "Konteks data perusahaan:\n" . json_encode($context, JSON_UNESCAPED_UNICODE) . "\n\nPertanyaan pengguna:\n" . $prompt;
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $groundedPrompt]],
        ];

        try {
            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";
            $response = Http::timeout(20)->post($endpoint, [
                'systemInstruction' => [
                    'parts' => [['text' => $systemInstruction]],
                ],
                'contents' => $contents,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Maaf, respon AI kosong.';
                $tokensUsed = $data['usageMetadata']['totalTokenCount'] ?? 150;

                return [
                    'content' => $reply,
                    'tokens_used' => $tokensUsed,
                    'grounding' => [
                        'provider' => 'Google Gemini API (' . $this->model . ')',
                        'timestamp' => now()->toIso8601String(),
                        'context_keys' => array_keys($context),
                    ],
                    'provider' => $this->getProviderName(),
                ];
            }

            Log::warning('Gemini API call returned non-200: ' . $response->body());
            throw new \RuntimeException('Gemini API responded with status: ' . $response->status());
        } catch (\Throwable $e) {
            Log::error('Gemini Provider Exception: ' . $e->getMessage());
            throw $e;
        }
    }
}
