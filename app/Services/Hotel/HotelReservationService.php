<?php

namespace App\Services\Hotel;

use App\Models\Hotel;
use App\Models\HotelCustomer;
use App\Models\HotelInvoice;
use App\Models\HotelPayment;
use App\Models\HotelReservation;
use App\Models\HotelReservationItem;
use App\Models\HotelRoomType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HotelReservationService
{
    protected ExchangeRateService $fxService;

    public function __construct(ExchangeRateService $fxService)
    {
        $this->fxService = $fxService;
    }

    /**
     * Create a reservation with multi-room, multi-hotel support and automatic financial calculation.
     */
    public function createReservation(array $data): HotelReservation
    {
        return DB::transaction(function () use ($data) {
            $latestFx = $this->fxService->getLatestRate('SAR', 'IDR');
            $rateSnapshot = (float) $latestFx['rate'];

            // Generate Reservation Number
            $reservationNumber = $data['reservation_number'] ?? ('RES-HTL-' . date('Ym') . '-' . str_pad((HotelReservation::count() + 1), 4, '0', STR_PAD_LEFT));

            $checkIn = Carbon::parse($data['check_in_date']);
            $checkOut = Carbon::parse($data['check_out_date']);
            $totalNights = max(1, $checkIn->diffInDays($checkOut));

            $reservation = HotelReservation::create([
                'reservation_number' => $reservationNumber,
                'customer_id' => $data['customer_id'],
                'contract_id' => $data['contract_id'] ?? null,
                'business_model' => $data['business_model'] ?? 'reseller',
                'booking_date' => $data['booking_date'] ?? now()->toDateString(),
                'check_in_date' => $checkIn->toDateString(),
                'check_out_date' => $checkOut->toDateString(),
                'total_nights' => $totalNights,
                'total_rooms' => 0,
                'total_guests' => $data['total_guests'] ?? 1,
                'exchange_rate_snapshot' => $rateSnapshot,
                'status' => $data['status'] ?? 'pending',
                'payment_status' => 'belum_bayar',
                'confirmation_reference' => $data['confirmation_reference'] ?? null,
                'special_requests' => $data['special_requests'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'handled_by' => $data['handled_by'] ?? auth()->id(),
            ]);

            $totalRooms = 0;
            $totalPurchaseSar = 0;
            $totalSellingSar = 0;
            $totalCommissionSar = 0;

            foreach ($data['items'] as $itemData) {
                $itemIn = Carbon::parse($itemData['check_in'] ?? $checkIn);
                $itemOut = Carbon::parse($itemData['check_out'] ?? $checkOut);
                $nights = max(1, $itemIn->diffInDays($itemOut));
                $qty = (int) ($itemData['room_quantity'] ?? 1);
                $roomNights = $qty * $nights;

                $purchaseRate = (float) ($itemData['purchase_rate_per_night_sar'] ?? 0);
                $sellingRate = (float) ($itemData['selling_rate_per_night_sar'] ?? 0);

                $costSar = $roomNights * $purchaseRate;
                $sellSar = $roomNights * $sellingRate;
                $commSar = (float) ($itemData['commission_amount_sar'] ?? 0);

                $totalRooms += $qty;
                $totalPurchaseSar += $costSar;
                $totalSellingSar += $sellSar;
                $totalCommissionSar += $commSar;

                HotelReservationItem::create([
                    'reservation_id' => $reservation->id,
                    'hotel_id' => $itemData['hotel_id'],
                    'room_type_id' => $itemData['room_type_id'],
                    'check_in' => $itemIn->toDateString(),
                    'check_out' => $itemOut->toDateString(),
                    'number_of_nights' => $nights,
                    'room_quantity' => $qty,
                    'room_nights' => $roomNights,
                    'purchase_rate_per_night_sar' => $purchaseRate,
                    'selling_rate_per_night_sar' => $sellingRate,
                    'subtotal_cost_sar' => $costSar,
                    'subtotal_selling_sar' => $sellSar,
                    'commission_amount_sar' => $commSar,
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            // Calculate profit based on business model
            $grossProfitSar = 0;
            if ($reservation->business_model === 'reseller') {
                $grossProfitSar = $totalSellingSar - $totalPurchaseSar;
            } elseif ($reservation->business_model === 'commission') {
                $grossProfitSar = $totalCommissionSar;
            } elseif ($reservation->business_model === 'allotment') {
                $grossProfitSar = $totalSellingSar - $totalPurchaseSar;
            }

            // Converted amounts in IDR using frozen rate snapshot
            $sellingIdr = round($totalSellingSar * $rateSnapshot, 2);
            $purchaseIdr = round($totalPurchaseSar * $rateSnapshot, 2);
            $grossProfitIdr = round($grossProfitSar * $rateSnapshot, 2);

            $reservation->update([
                'total_rooms' => $totalRooms,
                'total_purchase_cost_sar' => $totalPurchaseSar,
                'total_selling_price_sar' => $totalSellingSar,
                'commission_earned_sar' => $totalCommissionSar,
                'gross_profit_sar' => $grossProfitSar,
                'total_selling_price_idr' => $sellingIdr,
                'total_purchase_cost_idr' => $purchaseIdr,
                'gross_profit_idr' => $grossProfitIdr,
            ]);

            return $reservation->load(['customer', 'items.hotel', 'items.roomType']);
        });
    }

    /**
     * Cancel a reservation with audit history.
     */
    public function cancelReservation(HotelReservation $reservation, string $reason): HotelReservation
    {
        return DB::transaction(function () use ($reservation, $reason) {
            $reservation->update([
                'status' => 'cancelled',
                'internal_notes' => ($reservation->internal_notes ? $reservation->internal_notes . "\n" : '') . "Dibatalkan: {$reason}",
            ]);

            if ($reservation->invoice) {
                $reservation->invoice->update(['status' => 'batal']);
            }

            return $reservation;
        });
    }
}
