<?php

namespace App\Services\Hotel\Contracts;

interface ExchangeRateProviderInterface
{
    public function getProviderName(): string;

    /**
     * Get the live exchange rate for a given currency pair (e.g. SAR -> IDR).
     *
     * @return array [
     *   'rate' => float,
     *   'provider' => string,
     *   'timestamp' => string (ISO8601),
     *   'status' => 'live'|'delayed'|'fallback'
     * ]
     */
    public function getRate(string $baseCurrency = 'SAR', string $targetCurrency = 'IDR'): array;

    public function isAvailable(): bool;
}
