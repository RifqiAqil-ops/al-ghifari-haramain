<?php

namespace App\Services\Hotel;

use App\Models\HotelInvoice;
use App\Models\HotelInvoiceItem;
use App\Models\HotelReservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HotelInvoiceService
{
    protected ExchangeRateService $fxService;

    public function __construct(ExchangeRateService $fxService)
    {
        $this->fxService = $fxService;
    }

    /**
     * Generate hotel invoice strictly modeled after standard Excel hotel invoice.
     * Preserves rate snapshot, subtotal SAR, room nights, and converted IDR.
     */
    public function createInvoiceFromReservation(HotelReservation $reservation, array $options = []): HotelInvoice
    {
        return DB::transaction(function () use ($reservation, $options) {
            $reservation->load(['customer', 'items.hotel', 'items.roomType']);

            // Get live or snapshot FX rate
            $latestFx = $this->fxService->getLatestRate('SAR', 'IDR');
            $rateSnapshot = (float) ($options['exchange_rate'] ?? $reservation->exchange_rate_snapshot ?? $latestFx['rate']);
            $rateProvider = $latestFx['provider'] ?? 'ExchangeRate-API';

            $invoiceNumber = $options['invoice_number'] ?? ('INV-HTL-' . date('Ym') . '-' . str_pad((HotelInvoice::count() + 1), 4, '0', STR_PAD_LEFT));
            $invoiceDate = $options['invoice_date'] ?? now()->toDateString();
            $dueDate = $options['due_date'] ?? Carbon::parse($invoiceDate)->addDays($reservation->customer?->payment_terms_days ?? 14)->toDateString();

            $subtotalSar = (float) $reservation->total_selling_price_sar;
            $discountSar = (float) ($options['discount_amount'] ?? 0);
            $additionalChargesSar = (float) ($options['additional_charges'] ?? 0);

            $totalSar = $subtotalSar - $discountSar + $additionalChargesSar;
            $totalIdr = round($totalSar * $rateSnapshot, 2);

            $invoice = HotelInvoice::create([
                'invoice_number' => $invoiceNumber,
                'reservation_id' => $reservation->id,
                'customer_id' => $reservation->customer_id,
                'invoice_date' => $invoiceDate,
                'due_date' => $dueDate,
                'currency' => 'SAR',
                'subtotal_amount' => $subtotalSar,
                'discount_amount' => $discountSar,
                'additional_charges' => $additionalChargesSar,
                'total_amount_sar' => $totalSar,
                'exchange_rate_snapshot' => $rateSnapshot,
                'rate_provider_snapshot' => $rateProvider,
                'rate_timestamp_snapshot' => now(),
                'total_amount_idr' => $totalIdr,
                'paid_amount_sar' => 0,
                'remaining_amount_sar' => $totalSar,
                'paid_amount_idr' => 0,
                'remaining_amount_idr' => $totalIdr,
                'status' => 'issued',
                'payment_terms' => $options['payment_terms'] ?? "Pembayaran dapat dilakukan dalam SAR atau IDR sesuai kurs yang disepakati. Pelunasan maksimal H-14 sebelum check-in.",
                'notes' => $options['notes'] ?? $reservation->internal_notes,
                'created_by' => auth()->id(),
            ]);

            // Create Line Items matching the Excel Reference (Hotel, Room Type, Check-in, Check-out, Nights, Qty, Rate, Subtotal)
            foreach ($reservation->items as $item) {
                $subSar = (float) $item->subtotal_selling_sar;
                $subIdr = round($subSar * $rateSnapshot, 2);

                HotelInvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'hotel_id' => $item->hotel_id,
                    'hotel_name' => $item->hotel?->name ?? 'Hotel',
                    'room_type_name' => $item->roomType?->name ?? 'Standard Room',
                    'check_in' => $item->check_in,
                    'check_out' => $item->check_out,
                    'number_of_nights' => $item->number_of_nights,
                    'room_quantity' => $item->room_quantity,
                    'room_nights' => $item->room_nights,
                    'rate_per_night_sar' => $item->selling_rate_per_night_sar,
                    'subtotal_sar' => $subSar,
                    'subtotal_idr' => $subIdr,
                ]);
            }

            return $invoice->load(['items', 'customer', 'reservation']);
        });
    }

    /**
     * Recalculate draft invoice with latest available exchange rate.
     * Note: Issued invoices preserve historical snapshot.
     */
    public function recalculateDraftInvoice(HotelInvoice $invoice): HotelInvoice
    {
        if ($invoice->status !== 'draft') {
            throw new \DomainException('Hanya invoice berstatus draft yang dapat dihitung ulang dengan kurs terbaru.');
        }

        $latestFx = $this->fxService->getLatestRate('SAR', 'IDR');
        $rate = (float) $latestFx['rate'];

        $totalSar = (float) $invoice->total_amount_sar;
        $totalIdr = round($totalSar * $rate, 2);

        $invoice->update([
            'exchange_rate_snapshot' => $rate,
            'rate_provider_snapshot' => $latestFx['provider'],
            'rate_timestamp_snapshot' => now(),
            'total_amount_idr' => $totalIdr,
            'remaining_amount_idr' => $totalIdr - $invoice->paid_amount_idr,
        ]);

        return $invoice;
    }
}
