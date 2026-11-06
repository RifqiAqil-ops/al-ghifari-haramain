<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use App\Models\CompanySetting;
use App\Models\Departure;
use App\Models\DepartureChecklist;
use App\Models\Flight;
use App\Models\Itinerary;
use App\Models\ItineraryItem;
use App\Models\Package;
use App\Services\ActivityLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepartureController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $packageId = $request->input('package_id');
        $status = $request->input('status');

        $query = Departure::with(['package'])
            ->withCount(['registrations' => fn($q) => $q->where('status', '!=', 'batal')]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('airline', 'like', "%{$search}%")
                  ->orWhere('flight_number', 'like', "%{$search}%")
                  ->orWhere('leader_name', 'like', "%{$search}%");
            });
        }

        if ($packageId) {
            $query->where('package_id', $packageId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $departures = $query->orderBy('departure_date', 'asc')->paginate(10)->withQueryString();
        $packages = Package::where('status', 'aktif')->get();

        return view('departures.index', compact('departures', 'packages', 'search', 'packageId', 'status'));
    }

    public function create(): View
    {
        $packages = Package::where('status', 'aktif')->get();
        return view('departures.create', compact('packages'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:departures,code'],
            'package_id' => ['required', 'exists:packages,id'],
            'departure_date' => ['required', 'date'],
            'return_date' => ['required', 'date', 'after_or_equal:departure_date'],
            'origin_airport' => ['nullable', 'string', 'max:100'],
            'destination_airport' => ['nullable', 'string', 'max:100'],
            'airline' => ['nullable', 'string', 'max:100'],
            'flight_number' => ['nullable', 'string', 'max:50'],
            'departure_time' => ['nullable'],
            'arrival_time' => ['nullable'],
            'quota' => ['required', 'integer', 'min:1'],
            'leader_name' => ['nullable', 'string', 'max:255'],
            'leader_phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:persiapan,siap_berangkat,berangkat,dalam_perjalanan,pulang,selesai,dibatalkan'],
            'notes' => ['nullable', 'string'],
        ], [
            'code.required' => 'Kode keberangkatan wajib diisi.',
            'code.unique' => 'Kode keberangkatan sudah digunakan.',
            'package_id.required' => 'Paket perjalanan wajib dipilih.',
            'departure_date.required' => 'Tanggal keberangkatan wajib diisi.',
            'return_date.required' => 'Tanggal kepulangan wajib diisi.',
        ]);

        $departure = Departure::create($validated);

        // Pre-create default departure checklists
        $defaultChecklists = [
            ['item_name' => 'Verifikasi Paspor Asli Jamaah (Min. Masa Berlaku 7 Bulan)', 'category' => 'dokumen'],
            ['item_name' => 'Penerbitan Visa Umrah Elektronik & Tasreeh Raudhah', 'category' => 'visa'],
            ['item_name' => 'Konfirmasi Tiket Penerbangan Maskapai Group PNR', 'category' => 'tiket'],
            ['item_name' => 'Konfirmasi Reservasi Hotel Makkah & Madinah', 'category' => 'hotel'],
            ['item_name' => 'Distribusi Koper, Kain Ihram & Seragam Batik Jamaah', 'category' => 'perlengkapan'],
            ['item_name' => 'Pelaksanaan Manasik Umrah / Haji Akbar Pra-Keberangkatan', 'category' => 'logistik'],
            ['item_name' => 'Booking Bus Antar Kota Saudi (Jeddah-Madinah-Makkah)', 'category' => 'logistik'],
        ];

        foreach ($defaultChecklists as $chk) {
            DepartureChecklist::create([
                'departure_id' => $departure->id,
                'item_name' => $chk['item_name'],
                'category' => $chk['category'],
                'is_completed' => false,
            ]);
        }

        ActivityLogger::log('CREATE', 'Keberangkatan', "Membuat jadwal keberangkatan baru: {$departure->code} (Paket ID: {$departure->package_id})", null, $departure->toArray());

        return redirect()->route('departures.show', $departure)
            ->with('toast_success', 'Jadwal keberangkatan berhasil ditambahkan.');
    }

    public function show(Departure $departure): View
    {
        $departure->load([
            'package',
            'registrations' => fn($q) => $q->where('status', '!=', 'batal')->with(['jamaah', 'invoice']),
            'flights',
            'accommodations',
            'itineraries.items',
            'checklists.completedByUser',
            'expenses',
        ]);

        return view('departures.show', compact('departure'));
    }

    public function edit(Departure $departure): View
    {
        $packages = Package::where('status', 'aktif')->get();
        return view('departures.edit', compact('departure', 'packages'));
    }

    public function update(Request $request, Departure $departure): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('departures')->ignore($departure->id)],
            'package_id' => ['required', 'exists:packages,id'],
            'departure_date' => ['required', 'date'],
            'return_date' => ['required', 'date', 'after_or_equal:departure_date'],
            'origin_airport' => ['nullable', 'string', 'max:100'],
            'destination_airport' => ['nullable', 'string', 'max:100'],
            'airline' => ['nullable', 'string', 'max:100'],
            'flight_number' => ['nullable', 'string', 'max:50'],
            'departure_time' => ['nullable'],
            'arrival_time' => ['nullable'],
            'quota' => ['required', 'integer', 'min:1'],
            'leader_name' => ['nullable', 'string', 'max:255'],
            'leader_phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:persiapan,siap_berangkat,berangkat,dalam_perjalanan,pulang,selesai,dibatalkan'],
            'notes' => ['nullable', 'string'],
        ]);

        $oldData = $departure->toArray();
        $departure->update($validated);

        ActivityLogger::log('UPDATE', 'Keberangkatan', "Memperbarui jadwal keberangkatan: {$departure->code}", $oldData, $departure->toArray());

        return redirect()->route('departures.show', $departure)
            ->with('toast_success', 'Jadwal keberangkatan berhasil diperbarui.');
    }

    public function destroy(Departure $departure): RedirectResponse
    {
        $code = $departure->code;
        $departure->delete();

        ActivityLogger::log('DELETE', 'Keberangkatan', "Mengarsipkan / menghapus jadwal keberangkatan: {$code}");

        return redirect()->route('departures.index')
            ->with('toast_success', "Keberangkatan {$code} berhasil diarsipkan.");
    }

    public function updateStatus(Request $request, Departure $departure): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:persiapan,siap_berangkat,berangkat,dalam_perjalanan,pulang,selesai,dibatalkan'],
        ]);

        $oldStatus = $departure->status;
        $newStatus = $request->status;

        $departure->update(['status' => $newStatus]);

        ActivityLogger::log('UPDATE_STATUS', 'Keberangkatan', "Perubahan status keberangkatan {$departure->code}: dari {$oldStatus} ke {$newStatus}");

        return back()->with('toast_success', 'Status perjalanan keberangkatan berhasil diperbarui.');
    }

    public function updateChecklist(Request $request, DepartureChecklist $checklist): RedirectResponse
    {
        $isCompleted = $request->boolean('is_completed');

        $checklist->update([
            'is_completed' => $isCompleted,
            'completed_at' => $isCompleted ? now() : null,
            'completed_by' => $isCompleted ? Auth::id() : null,
        ]);

        ActivityLogger::log('CHECKLIST', 'Keberangkatan', "Memperbarui checklist '{$checklist->item_name}' (Status: " . ($isCompleted ? 'Selesai' : 'Belum') . ")");

        return back()->with('toast_success', 'Checklist keberangkatan berhasil diperbarui.');
    }

    public function printManifest(Departure $departure)
    {
        $departure->load([
            'package',
            'registrations' => fn($q) => $q->where('status', '!=', 'batal')->with(['jamaah']),
            'flights',
            'accommodations',
        ]);

        $setting = CompanySetting::first();

        $pdf = Pdf::loadView('departures.manifest-pdf', compact('departure', 'setting'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream('Manifest_Jamaah_' . $departure->code . '.pdf');
    }

    public function calendar(): View
    {
        $departures = Departure::with(['package'])
            ->where('status', '!=', 'dibatalkan')
            ->get();

        return view('departures.calendar', compact('departures'));
    }
}
