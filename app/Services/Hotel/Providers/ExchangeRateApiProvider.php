<?php

namespace App\Services\Hotel\Providers;

use App\Services\Hotel\Contracts\ExchangeRateProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateApiProvider implements ExchangeRateProviderInterface
{
    /**
     * Official 100% Free Open Access Endpoint (Zero Cost, No API Key Required)
     * Documentation: https://www.exchangerate-api.com/docs/free
     */
    protected string $endpoint;

    public function __construct()
    {
        $this->endpoint = 'https://open.er-api.com/v6/latest';
    }

    public function getProviderName(): string
    {
        return 'ExchangeRate-API Open Access';
    }

    public function isAvailable(): bool
    {
        return true;
    }

    /**
     * Fetch standard daily exchange rate from official open access endpoint.
     * Updates once daily per provider's official schedule.
     */
    public function getRate(string $baseCurrency = 'SAR', string $targetCurrency = 'IDR'): array
    {
        $url = rtrim($this->endpoint, '/') . '/' . strtoupper($baseCurrency);

        try {
            $response = Http::timeout(10)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                $rates = $data['rates'] ?? [];

                if (isset($rates[$targetCurrency])) {
                    $rate = (float) $rates[$targetCurrency];
                    
                    // Official publication timestamp from provider
                    $rawTime = $data['time_last_update_utc'] ?? null;
                    $actualTimestamp = $rawTime ? date('Y-m-d H:i:s', strtotime($rawTime)) : now()->toDateTimeString();
                    
                    $nextUpdateRaw = $data['time_next_update_utc'] ?? null;
                    $nextUpdate = $nextUpdateRaw ? date('Y-m-d H:i:s', strtotime($nextUpdateRaw)) : null;

                    // Evaluate data freshness based on 36h grace window for daily update
                    $ageHours = abs((time() - strtotime($actualTimestamp)) / 3600);
                    $status = ($ageHours <= 36.0) ? 'daily_stable' : 'stale';

                    return [
                        'rate' => $rate,
                        'provider' => $this->getProviderName(),
                        'provider_raw' => 'ExchangeRate-API',
                        'timestamp' => $actualTimestamp,
                        'next_update' => $nextUpdate,
                        'refresh_interval_minutes' => 1440, // Official 24-hour cycle
                        'status' => $status,
                        'is_fallback' => false,
                        'attribution' => 'Rates By ExchangeRate-API (https://www.exchangerate-api.com)',
                    ];
                }
            }

            Log::warning('ExchangeRateApiProvider failed response: ' . $response->body());
        } catch (\Throwable $e) {
            Log::warning('ExchangeRateApiProvider exception: ' . $e->getMessage());
        }

        // Transparent Fallback (Strictly labeled as FALLBACK, never as realtime)
        return [
            'rate' => 4150.0000,
            'provider' => $this->getProviderName() . ' (Offline Fallback)',
            'provider_raw' => 'ExchangeRate-API',
            'timestamp' => now()->toDateTimeString(),
            'next_update' => null,
            'refresh_interval_minutes' => null,
            'status' => 'fallback',
            'is_fallback' => true,
            'attribution' => 'Rates By ExchangeRate-API (https://www.exchangerate-api.com)',
        ];
    }
}

