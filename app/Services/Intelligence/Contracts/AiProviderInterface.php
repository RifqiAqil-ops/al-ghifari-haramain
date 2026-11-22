<?php

namespace App\Services\Intelligence\Contracts;

interface AiProviderInterface
{
    /**
     * Send prompt to AI and return structured response.
     *
     * @param string $prompt
     * @param array $context Grounded company data points
     * @param array $history Previous messages in conversation
     * @return array ['content' => string, 'tokens_used' => int, 'grounding' => array, 'provider' => string]
     */
    public function chat(string $prompt, array $context = [], array $history = []): array;

    /**
     * Check if provider is currently reachable and configured.
     */
    public function isAvailable(): bool;

    /**
     * Return provider identification name.
     */
    public function getProviderName(): string;
}
