<?php

namespace App\Http\Controllers;

use App\Models\Departure;
use App\Models\Equipment;
use App\Models\EquipmentDistribution;
use App\Models\EquipmentDistributionItem;
use App\Models\EquipmentMutation;
use App\Models\Jamaah;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EquipmentDistributionController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $departureId = $request->input('departure_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $status = $request->input('status');

        $query = EquipmentDistribution::with(['jamaah', 'departure', 'officer', 'items.equipment'])
            ->latest('distribution_date')
            ->latest('id');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('distribution_number', 'like', "%{$search}%")
                  ->orWhereHas('jamaah', function ($jq) use ($search) {
                      $jq->where('name', 'like', "%{$search}%")
                         ->orWhere('nik', 'like', "%{$search}%");
                  });
            });
        }

        if ($departureId) {
            $query->where('departure_id', $departureId);
        }

        if ($startDate) {
            $query->whereDate('distribution_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('distribution_date', '<=', $endDate);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $distributions = $query->paginate(15)->withQueryString();

        $departures = Departure::where('status', '!=', 'dibatalkan')
            ->latest('departure_date')
            ->take(30)
            ->get();

        // Metrics
        $totalDistributions = EquipmentDistribution::where('status', 'diserahkan')->count();
        $totalItemsGiven = (int) EquipmentDistributionItem::whereHas('distribution', fn($q) => $q->where('status', 'diserahkan'))->sum('quantity');
        $totalJamaahServed = EquipmentDistribution::where('status', 'diserahkan')->distinct('jamaah_id')->count('jamaah_id');

        return view('equipment.distributions.index', compact(
            'distributions',
            'departures',
            'search',
            'departureId',
            'startDate',
            'endDate',
            'status',
            'totalDistributions',
            'totalItemsGiven',
            'totalJamaahServed'
        ));
    }

    public function create(Request $request): View
    {
        $selectedJamaahId = $request->input('jamaah_id');
        $selectedJamaah = null;
        $suggestedDepartureId = null;

        if ($selectedJamaahId) {
            $selectedJamaah = Jamaah::with(['registrations.departure.package'])->find($selectedJamaahId);
            if ($selectedJamaah) {
                // Cari keberangkatan aktif dari pendaftaran terbaru
                $latestActiveReg = $selectedJamaah->registrations()
                    ->where('status', '!=', 'batal')
                    ->latest('id')
                    ->first();
                $suggestedDepartureId = $latestActiveReg?->departure_id;
            }
        }

        $jamaahs = Jamaah::where('status', 'aktif')->orderBy('name')->get();

        $departures = Departure::where('status', '!=', 'dibatalkan')
            ->latest('departure_date')
            ->take(30)
            ->get();

        $equipments = Equipment::with('category')
            ->active()
            ->orderBy('name')
            ->get();

        return view('equipment.distributions.create', compact(
            'jamaahs',
            'departures',
            'equipments',
            'selectedJamaahId',
            'selectedJamaah',
            'suggestedDepartureId'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'jamaah_id' => ['required', 'exists:jamaahs,id'],
            'departure_id' => ['nullable', 'exists:departures,id'],
            'distribution_date' => ['required', 'date'],
            'received_by' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.equipment_id' => ['required', 'exists:equipments,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ], [
            'items.required' => 'Pilih minimal satu jenis perlengkapan untuk didistribusikan.',
            'items.min' => 'Pilih minimal satu jenis perlengkapan untuk didistribusikan.',
            'items.*.quantity.min' => 'Jumlah barang yang didistribusikan minimal 1.',
        ]);

        $distribution = DB::transaction(function () use ($validated, $request) {
            $jamaah = Jamaah::findOrFail($validated['jamaah_id']);

            // 1. Verifikasi dan Kunci Stok (Row-level Locking)
            $lockedEquipments = [];
            foreach ($validated['items'] as $itemData) {
                $eqId = $itemData['equipment_id'];
                $qty = (int) $itemData['quantity'];

                /** @var Equipment $locked */
                $locked = Equipment::where('id', $eqId)->lockForUpdate()->firstOrFail();

                if ($locked->stock < $qty) {
                    throw ValidationException::withMessages([
                        'items' => "Stok untuk '{$locked->name}' tidak mencukupi. Tersedia: {$locked->stock} {$locked->unit}, diminta: {$qty} {$locked->unit}.",
                    ]);
                }

                $lockedEquipments[$eqId] = $locked;
            }

            // 2. Buat Transaksi Distribusi
            $distributionNumber = EquipmentDistribution::generateDistributionNumber();

            $distribution = EquipmentDistribution::create([
                'distribution_number' => $distributionNumber,
                'jamaah_id' => $validated['jamaah_id'],
                'departure_id' => $validated['departure_id'] ?? null,
                'distribution_date' => $validated['distribution_date'],
                'status' => 'diserahkan',
                'received_by' => !empty($validated['received_by']) ? $validated['received_by'] : $jamaah->name,
                'notes' => $validated['notes'] ?? null,
                'distributed_by' => Auth::id(),
            ]);

            // 3. Simpan Item & Kurangi Stok & Buat Mutasi Stok
            foreach ($validated['items'] as $itemData) {
                $eqId = $itemData['equipment_id'];
                $qty = (int) $itemData['quantity'];
                $itemNotes = $itemData['notes'] ?? null;

                $locked = $lockedEquipments[$eqId];
                $stockBefore = $locked->stock;
                $stockAfter = $stockBefore - $qty;

                // Kurangi stok
                $locked->decrement('stock', $qty);

                // Buat item rincian distribusi
                EquipmentDistributionItem::create([
                    'distribution_id' => $distribution->id,
                    'equipment_id' => $eqId,
                    'quantity' => $qty,
                    'notes' => $itemNotes,
                ]);

                // Buat mutasi keluar
                EquipmentMutation::create([
                    'equipment_id' => $eqId,
                    'type' => 'out',
                    'quantity' => $qty,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reference_type' => 'distribution',
                    'reference_id' => $distribution->id,
                    'notes' => "Distribusi No. {$distribution->distribution_number} ke jamaah {$jamaah->name}" . ($itemNotes ? " ({$itemNotes})" : ""),
                    'created_by' => Auth::id(),
                ]);
            }

            ActivityLogger::log(
                'DISTRIBUTE',
                'Perlengkapan',
                "Mendistribusikan " . count($validated['items']) . " jenis perlengkapan ke jamaah {$jamaah->name} (No: {$distribution->distribution_number})",
                null,
                $distribution->toArray()
            );

            return $distribution;
        });

        return redirect()->route('equipment.distributions.show', $distribution)
            ->with('toast_success', "Distribusi perlengkapan No. {$distribution->distribution_number} berhasil dicatat.");
    }

    public function show(EquipmentDistribution $distribution): View
    {
        $distribution->load(['jamaah', 'departure.package', 'officer', 'items.equipment.category']);
        return view('equipment.distributions.show', compact('distribution'));
    }

    public function cancel(Request $request, EquipmentDistribution $distribution): RedirectResponse
    {
        $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:500'],
        ]);

        if ($distribution->status === 'dibatalkan') {
            return back()->with('toast_error', 'Transaksi distribusi ini sudah dibatalkan sebelumnya.');
        }

        DB::transaction(function () use ($distribution, $request) {
            $distribution->load('items.equipment');

            // Kembalikan stok setiap item
            foreach ($distribution->items as $item) {
                /** @var Equipment $locked */
                $locked = Equipment::where('id', $item->equipment_id)->lockForUpdate()->firstOrFail();
                $stockBefore = $locked->stock;
                $stockAfter = $stockBefore + $item->quantity;

                $locked->increment('stock', $item->quantity);

                // Catat mutasi pengembalian stok
                EquipmentMutation::create([
                    'equipment_id' => $locked->id,
                    'type' => 'adjustment',
                    'quantity' => $item->quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reference_type' => 'distribution_cancelled',
                    'reference_id' => $distribution->id,
                    'notes' => "Pengembalian stok dari pembatalan distribusi No. {$distribution->distribution_number}. Alasan: {$request->cancellation_reason}",
                    'created_by' => Auth::id(),
                ]);
            }

            $oldData = $distribution->toArray();

            $distribution->update([
                'status' => 'dibatalkan',
                'notes' => trim(($distribution->notes ? $distribution->notes . "\n" : '') . "[DIBATALKAN] " . $request->cancellation_reason),
            ]);

            ActivityLogger::log(
                'CANCEL_DISTRIBUTION',
                'Perlengkapan',
                "Membatalkan distribusi perlengkapan No. {$distribution->distribution_number}. Alasan: {$request->cancellation_reason}",
                $oldData,
                $distribution->toArray()
            );
        });

        return back()->with('toast_success', "Distribusi No. {$distribution->distribution_number} berhasil dibatalkan dan stok telah dikembalikan.");
    }

    /**
     * Halaman Riwayat & Audit Mutasi Stok Seluruh Perlengkapan
     */
    public function mutations(Request $request): View
    {
        $type = $request->input('type');
        $equipmentId = $request->input('equipment_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = EquipmentMutation::with(['equipment.category', 'creator'])
            ->latest('id');

        if ($type) {
            $query->where('type', $type);
        }

        if ($equipmentId) {
            $query->where('equipment_id', $equipmentId);
        }

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $mutations = $query->paginate(20)->withQueryString();

        $equipments = Equipment::orderBy('name')->get();

        return view('equipment.mutations.index', compact(
            'mutations',
            'equipments',
            'type',
            'equipmentId',
            'startDate',
            'endDate'
        ));
    }
}
