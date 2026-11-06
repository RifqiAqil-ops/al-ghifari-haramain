<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\CompanySetting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $setting = CompanySetting::firstOrCreate(['id' => 1], [
            'company_name' => 'Al Ghifari Haramain',
            'brand_tagline' => 'UMROH & HAJI SPECIALIST',
            'registration_prefix' => 'JAM',
            'invoice_prefix' => 'INV',
            'receipt_prefix' => 'KWT',
        ]);

        $bankAccounts = BankAccount::latest()->get();

        return view('settings.index', compact('setting', 'bankAccounts'));
    }

    public function updateCompany(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'brand_tagline' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'website' => ['nullable', 'string', 'max:255'],
            'registration_prefix' => ['required', 'string', 'max:10'],
            'invoice_prefix' => ['required', 'string', 'max:10'],
            'receipt_prefix' => ['required', 'string', 'max:10'],
            'receipt_footer_note' => ['nullable', 'string'],
        ]);

        $setting = CompanySetting::firstOrCreate(['id' => 1]);
        $oldData = $setting->toArray();
        $setting->update($validated);

        ActivityLogger::log('UPDATE_SETTINGS', 'Pengaturan', "Memperbarui profil dan penomoran dokumen perusahaan Al Ghifari Haramain", $oldData, $setting->toArray());

        return back()->with('toast_success', 'Pengaturan profil perusahaan berhasil disimpan.');
    }

    public function storeBankAccount(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:50'],
            'account_name' => ['required', 'string', 'max:150'],
            'branch' => ['nullable', 'string', 'max:100'],
        ]);

        $validated['is_active'] = true;
        $bank = BankAccount::create($validated);

        ActivityLogger::log('CREATE_BANK', 'Pengaturan', "Menambahkan rekening bank: {$bank->bank_name} ({$bank->account_number})");

        return back()->with('toast_success', 'Rekening bank berhasil ditambahkan.');
    }

    public function updateBankAccount(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:50'],
            'account_name' => ['required', 'string', 'max:150'],
            'branch' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $bankAccount->update($validated);

        ActivityLogger::log('UPDATE_BANK', 'Pengaturan', "Memperbarui rekening bank: {$bankAccount->bank_name}");

        return back()->with('toast_success', 'Rekening bank berhasil diperbarui.');
    }

    public function deleteBankAccount(BankAccount $bankAccount): RedirectResponse
    {
        $bankName = $bankAccount->bank_name;
        $bankAccount->delete();

        ActivityLogger::log('DELETE_BANK', 'Pengaturan', "Menghapus rekening bank: {$bankName}");

        return back()->with('toast_success', "Rekening bank {$bankName} berhasil dihapus.");
    }
}
