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
            'discount' => ['nullable', 'numeric', 'min:0', 'lte:agreed_price'],
            'special_notes' => ['nullable', 'string'],
        ], [
            'jamaah_id.required' => 'Jamaah wajib dipilih.',
            'package_id.required' => 'Paket perjalanan wajib dipilih.',
            'departure_id.required' => 'Jadwal keberangkatan wajib dipilih.',
            'agreed_price.required' => 'Harga kesepakatan wajib diisi.',
            'discount.lte' => 'Potongan harga/diskon tidak boleh melebihi harga kesepakatan.',
        ]);

        $discount = (float) ($validated['discount'] ?? 0);
        $finalPrice = max(0, (float) $validated['agreed_price'] - $discount);

        DB::beginTransaction();
        try {
            // Lock target departure untuk serialisasi kuota dan validasi relasi
            $targetDeparture = Departure::where('id', $validated['departure_id'])->lockForUpdate()->first();

            if (!$targetDeparture) {
                DB::rollBack();
                return back()->withErrors(['departure_id' => 'Jadwal keberangkatan tidak ditemukan.'])->withInput();
            }

            if ((int) $targetDeparture->package_id !== (int) $validated['package_id']) {
                DB::rollBack();
                return back()->withErrors([
                    'departure_id' => 'Jadwal keberangkatan yang dipilih (' . $targetDeparture->code . ') tidak sesuai dengan paket perjalanan yang dipilih.',
                ])->withInput();
            }

            if (!in_array($targetDeparture->status, ['persiapan', 'siap_berangkat'])) {
                DB::rollBack();
                return back()->withErrors([
                    'departure_id' => 'Tidak dapat mendaftarkan jamaah ke jadwal keberangkatan dengan status "' . $targetDeparture->status_label . '".',
                ])->withInput();
            }

            $currentActiveCount = Registration::where('departure_id', $targetDeparture->id)
                ->where('status', '!=', 'batal')
                ->count();

            if ($currentActiveCount >= $targetDeparture->quota) {
                DB::rollBack();
                return back()->withErrors([
                    'departure_id' => "Kapasitas kloter keberangkatan ({$targetDeparture->code}) sudah penuh ({$currentActiveCount}/{$targetDeparture->quota} jamaah). Pendaftaran ditolak.",
                ])->withInput();
            }

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

            DB::commit();

            return redirect()->route('jamaah.show', $reg->jamaah_id)
                ->with('toast_success', 'Pendaftaran jamaah dan tagihan invoice otomatis berhasil dibuat.');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function show(Registration $registration): View
    {
        $registration->load(['jamaah', 'package', 'departure', 'invoice.payments']);
        return view('jamaah.registration-detail', compact('registration'));
    }

    public function edit(Registration $registration): View|RedirectResponse
    {
        // Registrasi yang sudah batal atau selesai tidak dapat diedit
        if (in_array($registration->status, ['batal', 'selesai'])) {
            return redirect()->route('registrations.show', $registration)
                ->with('toast_error', 'Registrasi dengan status "' . $registration->status_label . '" tidak dapat diedit.');
        }

        $registration->load(['jamaah', 'package', 'departure', 'invoice.validPayments']);

        $packages = Package::where('status', 'aktif')->orderBy('name')->get();

        // Tampilkan kloter aktif (persiapan & siap_berangkat) ATAU kloter yang sedang digunakan registrasi ini
        $departures = Departure::where(function ($query) use ($registration) {
                $query->whereIn('status', ['persiapan', 'siap_berangkat'])
                      ->orWhere('id', $registration->departure_id);
            })
            ->where('status', '!=', 'dibatalkan')
            ->orderBy('departure_date')
            ->get();

        return view('jamaah.registration-edit', compact('registration', 'packages', 'departures'));
    }

    public function update(Request $request, Registration $registration): RedirectResponse
    {
        // Guard: status akhir tidak dapat diedit
        if (in_array($registration->status, ['batal', 'selesai'])) {
            return back()->with('toast_error', 'Registrasi dengan status "' . $registration->status_label . '" tidak dapat diubah.');
        }

        $validated = $request->validate([
            'package_id'        => ['required', 'exists:packages,id'],
            'departure_id'      => ['required', 'exists:departures,id'],
            'registration_date' => ['required', 'date'],
            'room_type'         => ['required', 'in:quad,triple,double,single'],
            'agreed_price'      => ['required', 'numeric', 'min:0'],
            'discount'          => ['nullable', 'numeric', 'min:0', 'lte:agreed_price'],
            'status'            => ['required', 'in:terdaftar,proses,siap_berangkat,berangkat'],
            'special_notes'     => ['nullable', 'string', 'max:1000'],
        ], [
            'package_id.required'   => 'Paket perjalanan wajib dipilih.',
            'departure_id.required' => 'Jadwal keberangkatan wajib dipilih.',
            'agreed_price.required' => 'Harga kesepakatan wajib diisi.',
            'discount.lte'          => 'Potongan harga/diskon tidak boleh melebihi harga kesepakatan.',
            'status.in'             => 'Status yang dipilih tidak valid.',
        ]);

        $discount   = (float) ($validated['discount'] ?? 0);
        $finalPrice = max(0, (float) $validated['agreed_price'] - $discount);

        // Proteksi Overpayment: final_price tidak boleh di bawah pembayaran valid yang sudah masuk
        $actualPaid = 0;
        if ($registration->invoice) {
            $actualPaid = (float) $registration->invoice->validPayments()->sum('amount');
        }
        if ($finalPrice < $actualPaid) {
            return back()->withErrors([
                'agreed_price' => 'Biaya akhir (Rp ' . number_format($finalPrice, 0, ',', '.') . ') tidak boleh lebih rendah dari total pembayaran yang sudah diterima (Rp ' . number_format($actualPaid, 0, ',', '.') . '). Penurunan biaya di bawah total pembayaran harus diproses melalui pengajuan Refund tersendiri.',
            ])->withInput();
        }

        $priceChanged     = (float) $registration->final_price !== $finalPrice;
        $departureChanged = (int) $registration->departure_id !== (int) $validated['departure_id'];

        DB::beginTransaction();
        try {
            // Kunci baris registrasi yang sedang diubah terlebih dahulu (urutan kunci konsisten)
            $lockedReg = Registration::where('id', $registration->id)->lockForUpdate()->first();
            if (!$lockedReg || in_array($lockedReg->status, ['batal', 'selesai'])) {
                DB::rollBack();
                return back()->with('toast_error', 'Registrasi dengan status "' . $registration->status_label . '" tidak dapat diubah.');
            }

            // Kunci baris departure tujuan untuk serialisasi kuota dan validasi relasi
            $targetDeparture = Departure::where('id', $validated['departure_id'])->lockForUpdate()->first();

            if (!$targetDeparture) {
                DB::rollBack();
                return back()->withErrors(['departure_id' => 'Jadwal keberangkatan tidak ditemukan.'])->withInput();
            }

            if ((int) $targetDeparture->package_id !== (int) $validated['package_id']) {
                DB::rollBack();
                return back()->withErrors([
                    'departure_id' => 'Jadwal keberangkatan yang dipilih (' . $targetDeparture->code . ') tidak sesuai dengan paket perjalanan yang dipilih.',
                ])->withInput();
            }

            // Jika keberangkatan berpindah kloter
            if ($departureChanged) {
                // Jangan izinkan pindah ke kloter yang bukan persiapan / siap_berangkat
                if (!in_array($targetDeparture->status, ['persiapan', 'siap_berangkat'])) {
                    DB::rollBack();
                    return back()->withErrors([
                        'departure_id' => 'Tidak dapat memindahkan registrasi ke jadwal keberangkatan dengan status "' . $targetDeparture->status_label . '".',
                    ])->withInput();
                }

                // Proteksi Kuota: targetDeparture sudah dikunci eksklusif, hitung jamaah aktif di kloter tujuan
                $currentActiveCount = Registration::where('departure_id', $targetDeparture->id)
                    ->where('status', '!=', 'batal')
                    ->count();

                if ($currentActiveCount >= $targetDeparture->quota) {
                    DB::rollBack();
                    return back()->withErrors([
                        'departure_id' => "Kapasitas kloter keberangkatan tujuan ({$targetDeparture->code}) sudah penuh ({$currentActiveCount}/{$targetDeparture->quota} jamaah). Perpindahan ditolak.",
                    ])->withInput();
                }
            }

            $oldData = $lockedReg->toArray();

            $lockedReg->update([
                'package_id'        => $validated['package_id'],
                'departure_id'      => $validated['departure_id'],
                'registration_date' => $validated['registration_date'],
                'room_type'         => $validated['room_type'],
                'agreed_price'      => $validated['agreed_price'],
                'discount'          => $discount,
                'final_price'       => $finalPrice,
                'status'            => $validated['status'],
                'special_notes'     => $validated['special_notes'] ?? null,
            ]);

            // Jika biaya berubah, perbarui total_amount invoice dan hitung ulang sisa tagihan
            if ($priceChanged && $lockedReg->invoice) {
                $invoice = $lockedReg->invoice;
                $invoice->total_amount = $finalPrice;
                $invoice->save();
                $invoice->recalculateFinancials();
            }

            ActivityLogger::log(
                'UPDATE_REGISTRATION',
                'Pendaftaran',
                "Memperbarui data registrasi {$lockedReg->registration_number}" .
                ($departureChanged ? " (kloter dipindah ke {$targetDeparture->code})" : "") .
                ($priceChanged ? " (biaya diubah menjadi Rp " . number_format($finalPrice, 0, ',', '.') . ")" : ""),
                $oldData,
                $lockedReg->fresh()->toArray()
            );

            DB::commit();

            return redirect()->route('registrations.show', $lockedReg)
                ->with('toast_success', 'Data registrasi ' . $lockedReg->registration_number . ' berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
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
