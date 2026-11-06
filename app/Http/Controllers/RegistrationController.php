<?php

namespace App\Http\Controllers;

use App\Models\Departure;
use App\Models\Jamaah;
use App\Models\Package;
use App\Models\Registration;
use App\Services\ActivityLogger;
use App\Services\FinanceService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $query = Registration::with(['jamaah', 'package', 'departure', 'invoice']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('registration_number', 'like', "%{$search}%")
                  ->orWhereHas('jamaah', fn($jq) => $jq->where('name', 'like', "%{$search}%")->orWhere('nik', 'like', "%{$search}%"));
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $registrations = $query->latest()->paginate(10)->withQueryString();

        return view('jamaah.registrations-index', compact('registrations', 'search', 'status'));
    }

    public function create(Request $request): View
    {
        $jamaahs = Jamaah::where('status', 'aktif')->orderBy('name')->get();
        $packages = Package::where('status', 'aktif')->orderBy('name')->get();
        $departures = Departure::where('status', '!=', 'dibatalkan')
            ->where('departure_date', '>=', Carbon::today())
            ->orderBy('departure_date')
            ->get();

        $selectedJamaahId = $request->input('jamaah_id');

        return view('jamaah.register', compact('jamaahs', 'packages', 'departures', 'selectedJamaahId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'jamaah_id' => ['required', 'exists:jamaahs,id'],
            'package_id' => ['required', 'exists:packages,id'],
            'departure_id' => ['required', 'exists:departures,id'],
            'registration_date' => ['required', 'date'],
            'room_type' => ['required', 'in:quad,triple,double,single'],
            'agreed_price' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'special_notes' => ['nullable', 'string'],
        ], [
            'jamaah_id.required' => 'Jamaah wajib dipilih.',
            'package_id.required' => 'Paket perjalanan wajib dipilih.',
            'departure_id.required' => 'Jadwal keberangkatan wajib dipilih.',
            'agreed_price.required' => 'Harga kesepakatan wajib diisi.',
        ]);

        $discount = (float) ($validated['discount'] ?? 0);
        $finalPrice = max(0, (float) $validated['agreed_price'] - $discount);

        $registration = DB::transaction(function () use ($validated, $discount, $finalPrice) {
            $yearMonth = Carbon::now()->format('Ym');
            $count = Registration::whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                ->count() + 1;
            $regNumber = sprintf('REG-%s-%04d', $yearMonth, $count);

            $reg = Registration::create([
                'registration_number' => $regNumber,
                'jamaah_id' => $validated['jamaah_id'],
                'package_id' => $validated['package_id'],
                'departure_id' => $validated['departure_id'],
                'registration_date' => $validated['registration_date'],
                'room_type' => $validated['room_type'],
                'agreed_price' => $validated['agreed_price'],
                'discount' => $discount,
                'final_price' => $finalPrice,
                'status' => 'terdaftar',
                'special_notes' => $validated['special_notes'] ?? null,
            ]);

            // Auto-create invoice
            FinanceService::createInvoiceForRegistration($reg);

            ActivityLogger::log('REGISTER', 'Pendaftaran', "Mendaftarkan jamaah ID {$reg->jamaah_id} pada paket ID {$reg->package_id} (No: {$reg->registration_number})", null, $reg->toArray());

            return $reg;
        });

        return redirect()->route('jamaah.show', $registration->jamaah_id)
            ->with('toast_success', 'Pendaftaran jamaah dan tagihan invoice otomatis berhasil dibuat.');
    }

    public function show(Registration $registration): View
    {
        $registration->load(['jamaah', 'package', 'departure', 'invoice.payments']);
        return view('jamaah.registration-detail', compact('registration'));
    }

    public function cancel(Request $request, Registration $registration): RedirectResponse
    {
        $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($registration, $request) {
            $oldData = $registration->toArray();

            $registration->update([
                'status' => 'batal',
                'cancellation_reason' => $request->cancellation_reason,
                'cancelled_at' => now(),
            ]);

            if ($registration->invoice) {
                $registration->invoice->update(['status' => 'batal']);
            }

            ActivityLogger::log('CANCEL_REGISTRATION', 'Pendaftaran', "Membatalkan pendaftaran {$registration->registration_number}. Alasan: {$request->cancellation_reason}", $oldData, $registration->toArray());
        });

        return back()->with('toast_success', 'Pendaftaran berhasil dibatalkan.');
    }
}
