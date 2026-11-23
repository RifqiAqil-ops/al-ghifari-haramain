<?php

namespace App\Services\Hotel;

use App\Models\HotelAuditLog;
use App\Models\HotelExchangeRate;
use App\Services\Hotel\Contracts\ExchangeRateProviderInterface;
use App\Services\Hotel\Providers\ExchangeRateApiProvider;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ExchangeRateService
{
    protected ExchangeRateProviderInterface $provider;

    public function __construct(?ExchangeRateProviderInterface $provider = null)
    {
        $this->provider = $provider ?? new ExchangeRateApiProvider();
    }

    /**
     * Get the latest available rate (cached for 5 minutes, auto-refreshed, or manual override).
     */
    public function getLatestRate(string $baseCurrency = 'SAR', string $targetCurrency = 'IDR'): array
    {
        $cacheKey = "hotel_fx_rate_{$baseCurrency}_{$targetCurrency}";

        // 1. Check if there is an active manual override in database
        $latestRecord = HotelExchangeRate::where('base_currency', $baseCurrency)
            ->where('target_currency', $targetCurrency)
            ->latest('fetched_at')
            ->first();

        // If manual override was recorded within last 24 hours and not expired
        if ($latestRecord && $latestRecord->is_manual_override && (!$latestRecord->expires_at || $latestRecord->expires_at->isFuture())) {
            return [
                'rate' => (float) $latestRecord->rate,
                'provider' => 'Manual Override (' . ($latestRecord->user?->name ?? 'Admin') . ')',
                'status' => 'manual',
                'is_manual' => true,
                'fetched_at' => $latestRecord->fetched_at,
                'age_seconds' => abs(Carbon::now()->diffInSeconds($latestRecord->fetched_at)),
                'reason' => $latestRecord->override_reason,
            ];
        }

        // 2. Check cache (Application cache interval: 5 minutes)
        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            $cacheAgeSeconds = abs(Carbon::now()->diffInSeconds(Carbon::parse($cached['cached_at'] ?? now())));
            $ratePublicationAge = abs(Carbon::now()->diffInSeconds(Carbon::parse($cached['fetched_at'])));

            return array_merge($cached, [
                'cache_age_seconds' => $cacheAgeSeconds,
                'publication_age_seconds' => $ratePublicationAge,
                'is_cached' => true,
            ]);
        }

        // 3. Fetch fresh rate from provider
        return $this->forceRefreshRate($baseCurrency, $targetCurrency);
    }

    /**
     * Force refresh rate from provider and persist snapshot to database.
     */
    public function forceRefreshRate(string $baseCurrency = 'SAR', string $targetCurrency = 'IDR'): array
    {
        $cacheKey = "hotel_fx_rate_{$baseCurrency}_{$targetCurrency}";
        $fetched = $this->provider->getRate($baseCurrency, $targetCurrency);

        $record = HotelExchangeRate::create([
            'base_currency' => $baseCurrency,
            'target_currency' => $targetCurrency,
            'rate' => $fetched['rate'],
            'provider' => $fetched['provider'],
            'status' => $fetched['status'],
            'fetched_at' => Carbon::parse($fetched['timestamp']),
            'expires_at' => Carbon::now()->addMinutes(15),
            'is_manual_override' => false,
            'created_by' => Auth::id(),
        ]);

        $result = [
            'rate' => (float) $record->rate,
            'provider' => $record->provider,
            'status' => $record->status,
            'is_manual' => false,
            'is_fallback' => $fetched['is_fallback'] ?? false,
            'fetched_at' => $record->fetched_at,
            'cached_at' => now(),
            'next_update' => $fetched['next_update'] ?? null,
            'refresh_interval_minutes' => $fetched['refresh_interval_minutes'] ?? 5,
            'cache_age_seconds' => 0,
            'publication_age_seconds' => abs(Carbon::now()->diffInSeconds($record->fetched_at)),
        ];

        // Cache for 5 minutes (application cache interval)
        Cache::put($cacheKey, $result, Carbon::now()->addMinutes(5));

        return $result;
    }

    /**
     * Authorize manual rate override with audit log.
     */
    public function manualOverride(float $rate, string $reason, ?Carbon $expiresAt = null, string $baseCurrency = 'SAR', string $targetCurrency = 'IDR'): HotelExchangeRate
    {
        $user = Auth::user();

        $previous = $this->getLatestRate($baseCurrency, $targetCurrency);

        $record = HotelExchangeRate::create([
            'base_currency' => $baseCurrency,
            'target_currency' => $targetCurrency,
            'rate' => $rate,
            'provider' => 'Manual Override',
            'status' => 'manual',
            'fetched_at' => Carbon::now(),
            'expires_at' => $expiresAt ?? Carbon::now()->addHours(12),
            'is_manual_override' => true,
            'override_reason' => $reason,
            'created_by' => $user?->id,
        ]);

        // Clear cache so new rate takes effect immediately
        Cache::forget("hotel_fx_rate_{$baseCurrency}_{$targetCurrency}");

        // Log audit trail
        HotelAuditLog::create([
            'user_id' => $user?->id,
            'action' => 'exchange_rate_override',
            'entity_type' => HotelExchangeRate::class,
            'entity_id' => $record->id,
            'description' => "Kurs {$baseCurrency}/{$targetCurrency} di-override manual dari {$previous['rate']} ke {$rate}. Alasan: {$reason}",
            'old_values' => ['rate' => $previous['rate'], 'provider' => $previous['provider']],
            'new_values' => ['rate' => $rate, 'reason' => $reason],
            'ip_address' => request()->ip(),
        ]);

        return $record;
    }

    /**
     * Decimal-safe conversion from SAR to IDR.
     */
    public function convertSarToIdr(float $amountSar, ?float $rate = null): float
    {
        if ($rate === null) {
            $latest = $this->getLatestRate('SAR', 'IDR');
            $rate = $latest['rate'];
        }

        // BCMul or precise round
        return round($amountSar * $rate, 2);
    }

    /**
     * Decimal-safe conversion from IDR to SAR.
     */
    public function convertIdrToSar(float $amountIdr, ?float $rate = null): float
    {
        if ($rate === null) {
            $latest = $this->getLatestRate('SAR', 'IDR');
            $rate = $latest['rate'];
        }

        if ($rate <= 0) {
            throw new \InvalidArgumentException('Exchange rate must be greater than zero.');
        }

        return round($amountIdr / $rate, 2);
    }
}
