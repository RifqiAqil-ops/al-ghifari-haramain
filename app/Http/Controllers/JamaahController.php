<?php

namespace App\Http\Controllers;

use App\Exports\JamaahExport;
use App\Models\CompanySetting;
use App\Models\Jamaah;
use App\Models\JamaahContact;
use App\Models\JamaahDocument;
use App\Services\ActivityLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class JamaahController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $gender = $request->input('gender');
        $status = $request->input('status');

        $query = Jamaah::withCount(['registrations', 'documents']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('passport_number', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($gender) {
            $query->where('gender', $gender);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $jamaahs = $query->latest()->paginate(10)->withQueryString();

        return view('jamaah.index', compact('jamaahs', 'search', 'gender', 'status'));
    }

    public function create(): View
    {
        return view('jamaah.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'size:16', 'unique:jamaahs,nik'],
            'family_card_number' => ['nullable', 'string', 'size:16'],
            'passport_number' => ['nullable', 'string', 'max:20'],
            'passport_expiry_date' => ['nullable', 'date'],
            'passport_issued_place' => ['nullable', 'string', 'max:100'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['required', 'in:L,P'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_relation' => ['nullable', 'string', 'max:50'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'special_needs' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ], [
            'name.required' => 'Nama lengkap jamaah wajib diisi.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.size' => 'NIK harus berjumlah tepat 16 digit.',
            'nik.unique' => 'NIK sudah terdaftar dalam sistem.',
            'phone.required' => 'Nomor WhatsApp / HP wajib diisi.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
        ]);

        // Auto-generate unique registration number: JAM-YYYYMM-XXXX
        $yearMonth = Carbon::now()->format('Ym');
        $setting = CompanySetting::first();
        $prefix = $setting?->registration_prefix ?? 'JAM';

        $lastJamaah = Jamaah::withTrashed()
            ->where('registration_number', 'like', "{$prefix}-{$yearMonth}-%")
            ->orderByDesc('registration_number')
            ->first();

        $nextNumber = 1;
        if ($lastJamaah && preg_match('/(\d{4})$/', $lastJamaah->registration_number, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        // Ensure unique in case of existing numbers
        do {
            $registrationNumber = sprintf('%s-%s-%04d', $prefix, $yearMonth, $nextNumber);
            $exists = Jamaah::withTrashed()->where('registration_number', $registrationNumber)->exists();
            if ($exists) {
                $nextNumber++;
            }
        } while ($exists);

        $validated['registration_number'] = $registrationNumber;
        $validated['status'] = 'aktif';

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('jamaah_photos', 'public');
        }

        $jamaah = Jamaah::create($validated);

        // Emergency contact
        if ($request->filled('emergency_contact_name')) {
            JamaahContact::create([
                'jamaah_id' => $jamaah->id,
                'name' => $request->emergency_contact_name,
                'relationship' => $request->emergency_contact_relation ?? 'kerabat',
                'phone' => $request->emergency_contact_phone ?? $request->phone,
                'is_emergency_contact' => true,
            ]);
        }

        ActivityLogger::log('CREATE', 'Jamaah', "Mendaftarkan jamaah baru: {$jamaah->name} ({$jamaah->registration_number})", null, $jamaah->toArray());

        return redirect()->route('jamaah.show', $jamaah)
            ->with('toast_success', 'Data jamaah baru berhasil ditambahkan.');
    }

    public function show(Jamaah $jamaah): View
    {
        $jamaah->load([
            'documents',
            'contacts',
            'registrations.package',
            'registrations.departure',
            'registrations.invoice.validPayments',
            'whatsappMessages' => fn($q) => $q->latest()->take(5),
        ]);

        return view('jamaah.show', compact('jamaah'));
    }

    public function edit(Jamaah $jamaah): View
    {
        return view('jamaah.edit', compact('jamaah'));
    }

    public function update(Request $request, Jamaah $jamaah): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'size:16', Rule::unique('jamaahs')->ignore($jamaah->id)],
            'family_card_number' => ['nullable', 'string', 'size:16'],
            'passport_number' => ['nullable', 'string', 'max:20'],
            'passport_expiry_date' => ['nullable', 'date'],
            'passport_issued_place' => ['nullable', 'string', 'max:100'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['required', 'in:L,P'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_relation' => ['nullable', 'string', 'max:50'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'special_needs' => ['nullable', 'string'],
            'status' => ['required', 'in:aktif,nonaktif,arsip'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            if ($jamaah->photo && Storage::disk('public')->exists($jamaah->photo)) {
                Storage::disk('public')->delete($jamaah->photo);
            }
            $validated['photo'] = $request->file('photo')->store('jamaah_photos', 'public');
        }

        $oldData = $jamaah->toArray();
        $jamaah->update($validated);

        ActivityLogger::log('UPDATE', 'Jamaah', "Memperbarui data jamaah: {$jamaah->name} ({$jamaah->registration_number})", $oldData, $jamaah->toArray());

        return redirect()->route('jamaah.show', $jamaah)
            ->with('toast_success', 'Data jamaah berhasil diperbarui.');
    }

    public function destroy(Jamaah $jamaah): RedirectResponse
    {
        $name = $jamaah->name;
        $regNumber = $jamaah->registration_number;

        $jamaah->delete();

        ActivityLogger::log('DELETE', 'Jamaah', "Mengarsipkan / menghapus data jamaah: {$name} ({$regNumber})");

        return redirect()->route('jamaah.index')
            ->with('toast_success', "Data jamaah {$name} berhasil diarsipkan.");
    }

    public function uploadDocument(Request $request, Jamaah $jamaah): RedirectResponse
    {
        $request->validate([
            'document_type' => ['required', 'string'],
            'document_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'document_name' => ['nullable', 'string', 'max:255'],
        ], [
            'document_file.required' => 'File berkas wajib diunggah.',
            'document_file.max' => 'Ukuran berkas maksimal 5MB.',
        ]);

        $file = $request->file('document_file');
        $filePath = $file->store('jamaah_documents/' . $jamaah->id, 'public');

        $docName = $request->filled('document_name')
            ? $request->document_name
            : ucfirst(str_replace('_', ' ', $request->document_type)) . ' - ' . $jamaah->name;

        $doc = JamaahDocument::create([
            'jamaah_id' => $jamaah->id,
            'document_type' => $request->document_type,
            'document_name' => $docName,
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'file_type' => $file->getClientMimeType(),
            'status' => 'menunggu_verifikasi',
        ]);

        ActivityLogger::log('UPLOAD_DOCUMENT', 'Jamaah', "Mengunggah dokumen '{$docName}' untuk jamaah {$jamaah->name}");

        return back()->with('toast_success', 'Dokumen berhasil diunggah.');
    }

    public function verifyDocument(Request $request, JamaahDocument $document): RedirectResponse
    {
        $status = $request->input('status', 'diverifikasi');
        $note = $request->input('verification_note');

        $document->update([
            'status' => $status,
            'verification_note' => $note,
            'verified_at' => now(),
            'verified_by' => Auth::id(),
        ]);

        ActivityLogger::log('VERIFY_DOCUMENT', 'Jamaah', "Verifikasi dokumen '{$document->document_name}' jamaah {$document->jamaah->name}: Status {$status}");

        return back()->with('toast_success', 'Status dokumen berhasil diperbarui.');
    }

    public function downloadDocument(JamaahDocument $document)
    {
        if (!Storage::disk('public')->exists($document->file_path)) {
            return back()->with('toast_danger', 'File dokumen fisik tidak ditemukan di server.');
        }

        return Storage::disk('public')->download($document->file_path, $document->document_name);
    }

    public function printForm(Jamaah $jamaah): View
    {
        $setting = CompanySetting::first();
        return view('jamaah.print-form', compact('jamaah', 'setting'));
    }

    public function exportPdf(Request $request)
    {
        $jamaahs = Jamaah::with(['registrations.package'])->latest()->get();
        $setting = CompanySetting::first();

        $pdf = Pdf::loadView('jamaah.export-pdf', compact('jamaahs', 'setting'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('Data_Jamaah_Al_Ghifari_' . date('Ymd_His') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $filename = 'Data_Jamaah_Al_Ghifari_' . date('Ymd_His') . '.xlsx';
        $jamaahs = Jamaah::with(['registrations.package'])->latest()->get();
        $setting = CompanySetting::first();

        return Excel::download(new JamaahExport($jamaahs, $setting), $filename);
    }
}
