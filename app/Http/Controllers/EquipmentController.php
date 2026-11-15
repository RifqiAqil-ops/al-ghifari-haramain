<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentMutation;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EquipmentController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');
        $lowStock = $request->boolean('low_stock');
        $status = $request->input('status');

        $query = Equipment::with('category')->withCount(['mutations']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($lowStock) {
            $query->whereColumn('stock', '<=', 'min_stock');
        }

        if ($status !== null && $status !== '') {
            $query->where('is_active', $status === '1');
        }

        $equipments = $query->orderBy('name')->paginate(15)->withQueryString();

        $categories = EquipmentCategory::orderBy('name')->get();

        // Metrics
        $totalItems = Equipment::count();
        $totalAvailableStock = (int) Equipment::sum('stock');
        $lowStockCount = Equipment::whereColumn('stock', '<=', 'min_stock')->count();
        $totalMutationsCount = EquipmentMutation::count();

        return view('equipment.index', compact(
            'equipments',
            'categories',
            'search',
            'categoryId',
            'lowStock',
            'status',
            'totalItems',
            'totalAvailableStock',
            'lowStockCount',
            'totalMutationsCount'
        ));
    }

    public function create(): View
    {
        $categories = EquipmentCategory::orderBy('name')->get();
        return view('equipment.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'exists:equipment_categories,id'],
            'code' => ['required', 'string', 'max:50', 'unique:equipments,code'],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:50'],
            'initial_stock' => ['required', 'integer', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ]);

        $equipment = DB::transaction(function () use ($validated) {
            $equipment = Equipment::create([
                'category_id' => $validated['category_id'] ?? null,
                'code' => strtoupper($validated['code']),
                'name' => $validated['name'],
                'unit' => strtolower($validated['unit']),
                'stock' => $validated['initial_stock'],
                'min_stock' => $validated['min_stock'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            if ($validated['initial_stock'] > 0) {
                EquipmentMutation::create([
                    'equipment_id' => $equipment->id,
                    'type' => 'in',
                    'quantity' => $validated['initial_stock'],
                    'stock_before' => 0,
                    'stock_after' => $validated['initial_stock'],
                    'reference_type' => 'restock',
                    'notes' => 'Pencatatan saldo awal saat barang ditambahkan',
                    'created_by' => Auth::id(),
                ]);
            }

            ActivityLogger::log('CREATE', 'Perlengkapan', "Menambahkan perlengkapan baru {$equipment->name} ({$equipment->code})", null, $equipment->toArray());

            return $equipment;
        });

        return redirect()->route('equipment.index')
            ->with('toast_success', "Perlengkapan '{$equipment->name}' berhasil ditambahkan.");
    }

    public function show(Equipment $equipment): View
    {
        $equipment->load('category');

        $mutations = $equipment->mutations()
            ->with('creator')
            ->paginate(15);

        $stats = [
            'total_in' => (int) $equipment->mutations()->where('type', 'in')->sum('quantity'),
            'total_out' => (int) $equipment->mutations()->where('type', 'out')->sum('quantity'),
            'total_damaged' => (int) $equipment->mutations()->where('type', 'damaged')->sum('quantity'),
        ];

        return view('equipment.show', compact('equipment', 'mutations', 'stats'));
    }

    public function edit(Equipment $equipment): View
    {
        $categories = EquipmentCategory::orderBy('name')->get();
        return view('equipment.edit', compact('equipment', 'categories'));
    }

    public function update(Request $request, Equipment $equipment): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'exists:equipment_categories,id'],
            'code' => ['required', 'string', 'max:50', Rule::unique('equipments', 'code')->ignore($equipment->id)],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:50'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ]);

        $oldData = $equipment->toArray();

        $equipment->update([
            'category_id' => $validated['category_id'] ?? null,
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'unit' => strtolower($validated['unit']),
            'min_stock' => $validated['min_stock'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        ActivityLogger::log('UPDATE', 'Perlengkapan', "Memperbarui data perlengkapan {$equipment->name}", $oldData, $equipment->toArray());

        return redirect()->route('equipment.index')
            ->with('toast_success', "Perlengkapan '{$equipment->name}' berhasil diperbarui.");
    }

    public function destroy(Equipment $equipment): RedirectResponse
    {
        // Cek jika barang memiliki relasi distribusi aktif
        $distributionCount = $equipment->distributionItems()->count();

        if ($distributionCount > 0) {
            $equipment->update(['is_active' => false]);
            ActivityLogger::log('DEACTIVATE', 'Perlengkapan', "Menonaktifkan perlengkapan {$equipment->name} karena memiliki histori distribusi", null, $equipment->toArray());
            return redirect()->route('equipment.index')
                ->with('toast_success', "Perlengkapan '{$equipment->name}' memiliki {$distributionCount} riwayat distribusi, status barang dinonaktifkan (diarsipkan).");
        }

        $equipmentName = $equipment->name;
        $equipment->delete();

        ActivityLogger::log('DELETE', 'Perlengkapan', "Menghapus perlengkapan {$equipmentName}", $equipment->toArray(), null);

        return redirect()->route('equipment.index')
            ->with('toast_success', "Perlengkapan '{$equipmentName}' berhasil dihapus.");
    }

    /**
     * Mutasi Stok: Stok Masuk (Restock), Stok Rusak (Damaged), Penyesuaian (Adjustment)
     */
    public function mutateStock(Request $request, Equipment $equipment): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['in', 'damaged', 'adjustment'])],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($equipment, $validated) {
            // Row lock untuk proteksi konkurensi stok
            $lockedEquipment = Equipment::where('id', $equipment->id)->lockForUpdate()->firstOrFail();

            $qty = $validated['quantity'];
            $stockBefore = $lockedEquipment->stock;

            if ($validated['type'] === 'in') {
                $stockAfter = $stockBefore + $qty;
                $lockedEquipment->increment('stock', $qty);
                $refType = 'restock';
            } elseif ($validated['type'] === 'damaged') {
                if ($stockBefore < $qty) {
                    throw ValidationException::withMessages([
                        'quantity' => "Jumlah barang rusak ({$qty}) melebihi stok yang tersedia saat ini ({$stockBefore}).",
                    ]);
                }
                $stockAfter = $stockBefore - $qty;
                $lockedEquipment->decrement('stock', $qty);
                $refType = 'damage_report';
            } else { // adjustment
                // jika adjustment, quantity adalah selisih atau penyesuaian manual
                $mode = $request->input('adjustment_mode', 'add'); // add or subtract
                if ($mode === 'subtract') {
                    if ($stockBefore < $qty) {
                        throw ValidationException::withMessages([
                            'quantity' => "Pengurangan penyesuaian ({$qty}) melebihi stok yang tersedia ({$stockBefore}).",
                        ]);
                    }
                    $stockAfter = $stockBefore - $qty;
                    $lockedEquipment->decrement('stock', $qty);
                } else {
                    $stockAfter = $stockBefore + $qty;
                    $lockedEquipment->increment('stock', $qty);
                }
                $refType = 'adjustment';
            }

            EquipmentMutation::create([
                'equipment_id' => $lockedEquipment->id,
                'type' => $validated['type'],
                'quantity' => $qty,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference_type' => $refType,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            ActivityLogger::log('STOCK_MUTATION', 'Perlengkapan', "Mutasi {$validated['type']} sejumlah {$qty} {$lockedEquipment->unit} pada {$lockedEquipment->name} (Stok: {$stockBefore} -> {$stockAfter})");
        });

        return back()->with('toast_success', 'Mutasi stok berhasil dicatat.');
    }
}
