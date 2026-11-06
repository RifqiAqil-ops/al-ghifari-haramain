<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\PackageFacility;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $type = $request->input('type'); // umrah, haji
        $status = $request->input('status');

        $query = Package::withCount(['departures', 'registrations']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('airline', 'like', "%{$search}%")
                  ->orWhere('hotel_makkah', 'like', "%{$search}%");
            });
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $packages = $query->latest()->paginate(9)->withQueryString();

        return view('packages.index', compact('packages', 'search', 'type', 'status'));
    }

    public function create(): View
    {
        return view('packages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:packages,code'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:umrah,haji'],
            'program_category' => ['nullable', 'string', 'max:100'],
            'haji_year' => ['nullable', 'integer'],
            'price' => ['required', 'numeric', 'min:0'],
            'quad_price' => ['nullable', 'numeric', 'min:0'],
            'triple_price' => ['nullable', 'numeric', 'min:0'],
            'double_price' => ['nullable', 'numeric', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'quota' => ['required', 'integer', 'min:1'],
            'airline' => ['nullable', 'string', 'max:100'],
            'hotel_makkah' => ['nullable', 'string', 'max:100'],
            'hotel_makkah_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'hotel_madinah' => ['nullable', 'string', 'max:100'],
            'hotel_madinah_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'start_period' => ['nullable', 'date'],
            'end_period' => ['nullable', 'date', 'after_or_equal:start_period'],
            'description' => ['nullable', 'string'],
            'is_promo' => ['boolean'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:draft,aktif,penuh,selesai,arsip'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'facilities' => ['nullable', 'array'],
        ], [
            'code.required' => 'Kode paket wajib diisi.',
            'code.unique' => 'Kode paket sudah digunakan.',
            'name.required' => 'Nama paket wajib diisi.',
            'price.required' => 'Harga paket wajib diisi.',
            'duration_days.required' => 'Durasi perjalanan wajib diisi.',
            'quota.required' => 'Kuota wajib diisi.',
        ]);

        $validated['is_promo'] = $request->boolean('is_promo');
        $validated['discount_amount'] = $validated['is_promo'] ? ($validated['discount_amount'] ?? 0) : 0;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('package_images', 'public');
        }

        $package = Package::create($validated);

        if ($request->filled('facilities')) {
            foreach ($request->input('facilities') as $fac) {
                if (!empty(trim($fac))) {
                    PackageFacility::create([
                        'package_id' => $package->id,
                        'name' => trim($fac),
                        'is_included' => true,
                    ]);
                }
            }
        }

        ActivityLogger::log('CREATE', 'Paket', "Menambahkan paket perjalanan baru: {$package->name} ({$package->code})", null, $package->toArray());

        return redirect()->route('packages.show', $package)
            ->with('toast_success', 'Paket perjalanan berhasil ditambahkan.');
    }

    public function show(Package $package): View
    {
        $package->load([
            'facilities',
            'departures' => fn($q) => $q->withCount('registrations')->orderBy('departure_date', 'asc'),
            'registrations.jamaah',
            'registrations.departure',
            'registrations.invoice',
        ]);

        return view('packages.show', compact('package'));
    }

    public function edit(Package $package): View
    {
        $package->load('facilities');
        return view('packages.edit', compact('package'));
    }

    public function update(Request $request, Package $package): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('packages')->ignore($package->id)],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:umrah,haji'],
            'program_category' => ['nullable', 'string', 'max:100'],
            'haji_year' => ['nullable', 'integer'],
            'price' => ['required', 'numeric', 'min:0'],
            'quad_price' => ['nullable', 'numeric', 'min:0'],
            'triple_price' => ['nullable', 'numeric', 'min:0'],
            'double_price' => ['nullable', 'numeric', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'quota' => ['required', 'integer', 'min:1'],
            'airline' => ['nullable', 'string', 'max:100'],
            'hotel_makkah' => ['nullable', 'string', 'max:100'],
            'hotel_makkah_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'hotel_madinah' => ['nullable', 'string', 'max:100'],
            'hotel_madinah_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'start_period' => ['nullable', 'date'],
            'end_period' => ['nullable', 'date', 'after_or_equal:start_period'],
            'description' => ['nullable', 'string'],
            'is_promo' => ['boolean'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:draft,aktif,penuh,selesai,arsip'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'facilities' => ['nullable', 'array'],
        ]);

        $validated['is_promo'] = $request->boolean('is_promo');
        $validated['discount_amount'] = $validated['is_promo'] ? ($validated['discount_amount'] ?? 0) : 0;

        if ($request->hasFile('image')) {
            if ($package->image && Storage::disk('public')->exists($package->image)) {
                Storage::disk('public')->delete($package->image);
            }
            $validated['image'] = $request->file('image')->store('package_images', 'public');
        }

        $oldData = $package->toArray();
        $package->update($validated);

        if ($request->has('facilities')) {
            $package->facilities()->delete();
            foreach ($request->input('facilities') as $fac) {
                if (!empty(trim($fac))) {
                    PackageFacility::create([
                        'package_id' => $package->id,
                        'name' => trim($fac),
                        'is_included' => true,
                    ]);
                }
            }
        }

        ActivityLogger::log('UPDATE', 'Paket', "Memperbarui paket perjalanan: {$package->name} ({$package->code})", $oldData, $package->toArray());

        return redirect()->route('packages.show', $package)
            ->with('toast_success', 'Paket perjalanan berhasil diperbarui.');
    }

    public function destroy(Package $package): RedirectResponse
    {
        $name = $package->name;
        $code = $package->code;

        $package->delete();

        ActivityLogger::log('DELETE', 'Paket', "Mengarsipkan / menghapus paket perjalanan: {$name} ({$code})");

        return redirect()->route('packages.index')
            ->with('toast_success', "Paket {$name} berhasil diarsipkan.");
    }

    public function duplicate(Package $package): RedirectResponse
    {
        $newCode = $package->code . '-COPY-' . strtoupper(substr(uniqid(), -4));
        $newName = $package->name . ' (Salinan)';

        $newPackage = $package->replicate();
        $newPackage->code = $newCode;
        $newPackage->name = $newName;
        $newPackage->status = 'draft';
        $newPackage->save();

        foreach ($package->facilities as $fac) {
            PackageFacility::create([
                'package_id' => $newPackage->id,
                'name' => $fac->name,
                'is_included' => $fac->is_included,
            ]);
        }

        ActivityLogger::log('DUPLICATE', 'Paket', "Menduplikasi paket {$package->name} menjadi {$newName} ({$newCode})");

        return redirect()->route('packages.edit', $newPackage)
            ->with('toast_success', 'Paket berhasil disalin sebagai draf baru.');
    }
}
