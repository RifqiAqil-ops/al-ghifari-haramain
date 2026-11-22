<?php

namespace App\Services\Intelligence;

use App\Services\Intelligence\Contracts\AiProviderInterface;
use App\Services\Intelligence\Providers\GeminiProvider;
use App\Services\Intelligence\Providers\LocalHeuristicProvider;
use Illuminate\Support\Facades\Log;

class AiManager
{
    protected LocalHeuristicProvider $localProvider;
    protected GeminiProvider $geminiProvider;

    public function __construct()
    {
        $this->localProvider = new LocalHeuristicProvider();
        $this->geminiProvider = new GeminiProvider();
    }

    /**
     * Resolve the active AI provider.
     * Prefers Gemini if configured, otherwise falls back gracefully to Local Heuristic.
     */
    public function getProvider(): AiProviderInterface
    {
        if ($this->geminiProvider->isAvailable()) {
            return $this->geminiProvider;
        }

        return $this->localProvider;
    }

    /**
     * Send chat prompt with automatic fallback on failure.
     */
    public function chat(string $prompt, array $context = [], array $history = []): array
    {
        $provider = $this->getProvider();

        try {
            return $provider->chat($prompt, $context, $history);
        } catch (\Throwable $e) {
            Log::warning("AI Provider {$provider->getProviderName()} failed. Falling back to Local Heuristic: " . $e->getMessage());
            return $this->localProvider->chat($prompt, $context, $history);
        }
    }
}
