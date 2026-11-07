<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartureController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\JamaahController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WhatsAppController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Al Ghifari Haramain Management System
|--------------------------------------------------------------------------
*/

// Redirect root to dashboard or login
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // 1. Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    // Profile & Password (available to all logged-in users)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // 2. Modul Jamaah
    Route::middleware('permission:jamaah.export')->group(function () {
        Route::get('/jamaah/export/pdf', [JamaahController::class, 'exportPdf'])->name('jamaah.export.pdf');
        Route::get('/jamaah/export/excel', [JamaahController::class, 'exportExcel'])->name('jamaah.export.excel');
        Route::get('/jamaahs/export-pdf', [JamaahController::class, 'exportPdf'])->name('jamaahs.export-pdf');
        Route::get('/jamaahs/export-excel', [JamaahController::class, 'exportExcel'])->name('jamaahs.export-excel');
    });

    Route::middleware('permission:jamaah.create')->group(function () {
        Route::get('/jamaah/create', [JamaahController::class, 'create'])->name('jamaah.create');
        Route::get('/jamaahs/create', [JamaahController::class, 'create'])->name('jamaahs.create');
        Route::post('/jamaah', [JamaahController::class, 'store'])->name('jamaah.store');
        Route::post('/jamaahs', [JamaahController::class, 'store'])->name('jamaahs.store');

        Route::get('/registrations/create', [RegistrationController::class, 'create'])->name('registrations.create');
        Route::get('/jamaahs/register', [RegistrationController::class, 'create'])->name('jamaahs.register');
        Route::post('/registrations', [RegistrationController::class, 'store'])->name('registrations.store');
    });

    Route::middleware('permission:jamaah.document')->group(function () {
        Route::post('/jamaah/{jamaah}/documents', [JamaahController::class, 'uploadDocument'])->name('jamaah.documents.upload')->whereNumber('jamaah');
        Route::post('/jamaah/documents/{document}/verify', [JamaahController::class, 'verifyDocument'])->name('jamaah.documents.verify')->whereNumber('document');
        Route::get('/jamaah/documents/{document}/download', [JamaahController::class, 'downloadDocument'])->name('jamaah.documents.download')->whereNumber('document');
    });

    Route::middleware('permission:jamaah.edit')->group(function () {
        Route::get('/jamaah/{jamaah}/edit', [JamaahController::class, 'edit'])->name('jamaah.edit')->whereNumber('jamaah');
        Route::get('/jamaahs/{jamaah}/edit', [JamaahController::class, 'edit'])->name('jamaahs.edit')->whereNumber('jamaah');
        Route::put('/jamaah/{jamaah}', [JamaahController::class, 'update'])->name('jamaah.update')->whereNumber('jamaah');
        Route::put('/jamaahs/{jamaah}', [JamaahController::class, 'update'])->name('jamaahs.update')->whereNumber('jamaah');
        Route::patch('/jamaah/{jamaah}', [JamaahController::class, 'update'])->whereNumber('jamaah');
        Route::patch('/jamaahs/{jamaah}', [JamaahController::class, 'update'])->whereNumber('jamaah');
        Route::post('/registrations/{registration}/cancel', [RegistrationController::class, 'cancel'])->name('registrations.cancel')->whereNumber('registration');
    });

    Route::middleware('permission:jamaah.delete')->group(function () {
        Route::delete('/jamaah/{jamaah}', [JamaahController::class, 'destroy'])->name('jamaah.destroy')->whereNumber('jamaah');
        Route::delete('/jamaahs/{jamaah}', [JamaahController::class, 'destroy'])->name('jamaahs.destroy')->whereNumber('jamaah');
    });

    Route::middleware('permission:jamaah.view')->group(function () {
        Route::get('/jamaah', [JamaahController::class, 'index'])->name('jamaah.index');
        Route::get('/jamaahs', [JamaahController::class, 'index'])->name('jamaahs.index');
        Route::get('/jamaah/{jamaah}', [JamaahController::class, 'show'])->name('jamaah.show')->whereNumber('jamaah');
        Route::get('/jamaahs/{jamaah}', [JamaahController::class, 'show'])->name('jamaahs.show')->whereNumber('jamaah');
        Route::get('/jamaah/{jamaah}/print-form', [JamaahController::class, 'printForm'])->name('jamaah.print-form')->whereNumber('jamaah');

        Route::get('/registrations', [RegistrationController::class, 'index'])->name('registrations.index');
        Route::get('/registrations/{registration}', [RegistrationController::class, 'show'])->name('registrations.show')->whereNumber('registration');
    });

    // 3. Modul Paket
    Route::middleware('permission:package.create')->group(function () {
        Route::get('/packages/create', [PackageController::class, 'create'])->name('packages.create');
        Route::post('/packages', [PackageController::class, 'store'])->name('packages.store');
        Route::post('/packages/{package}/duplicate', [PackageController::class, 'duplicate'])->name('packages.duplicate')->whereNumber('package');
    });

    Route::middleware('permission:package.edit')->group(function () {
        Route::get('/packages/{package}/edit', [PackageController::class, 'edit'])->name('packages.edit')->whereNumber('package');
        Route::put('/packages/{package}', [PackageController::class, 'update'])->name('packages.update')->whereNumber('package');
        Route::patch('/packages/{package}', [PackageController::class, 'update'])->whereNumber('package');
    });

    Route::middleware('permission:package.delete')->group(function () {
        Route::delete('/packages/{package}', [PackageController::class, 'destroy'])->name('packages.destroy')->whereNumber('package');
    });

    Route::middleware('permission:package.view')->group(function () {
        Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');
        Route::get('/packages/{package}', [PackageController::class, 'show'])->name('packages.show')->whereNumber('package');
    });

    // 4. Modul Keberangkatan & Manifest
    Route::middleware('permission:departure.create')->group(function () {
        Route::get('/departures/create', [DepartureController::class, 'create'])->name('departures.create');
        Route::post('/departures', [DepartureController::class, 'store'])->name('departures.store');
    });

    Route::middleware('permission:departure.manifest')->group(function () {
        Route::get('/departures/{departure}/manifest-pdf', [DepartureController::class, 'printManifest'])->name('departures.manifest')->whereNumber('departure');
        Route::get('/departures/{departure}/manifest', [DepartureController::class, 'printManifest'])->name('departures.manifest-pdf')->whereNumber('departure');
    });

    Route::middleware('permission:departure.edit')->group(function () {
        Route::get('/departures/{departure}/edit', [DepartureController::class, 'edit'])->name('departures.edit')->whereNumber('departure');
        Route::put('/departures/{departure}', [DepartureController::class, 'update'])->name('departures.update')->whereNumber('departure');
        Route::patch('/departures/{departure}', [DepartureController::class, 'update'])->whereNumber('departure');
        Route::patch('/departures/{departure}/status', [DepartureController::class, 'updateStatus'])->name('departures.status')->whereNumber('departure');
        Route::patch('/departures/{departure}/update-status', [DepartureController::class, 'updateStatus'])->name('departures.update-status')->whereNumber('departure');
        Route::patch('/departures/checklist/{checklist}', [DepartureController::class, 'updateChecklist'])->name('departures.checklist')->whereNumber('checklist');
        Route::patch('/departures/checklist/{checklist}/update', [DepartureController::class, 'updateChecklist'])->name('departures.checklist.update')->whereNumber('checklist');
    });

    Route::middleware('permission:departure.delete')->group(function () {
        Route::delete('/departures/{departure}', [DepartureController::class, 'destroy'])->name('departures.destroy')->whereNumber('departure');
    });

    Route::middleware('permission:departure.view')->group(function () {
        Route::get('/departures', [DepartureController::class, 'index'])->name('departures.index');
        Route::get('/departures-calendar', [DepartureController::class, 'calendar'])->name('departures.calendar');
        Route::get('/departures/{departure}', [DepartureController::class, 'show'])->name('departures.show')->whereNumber('departure');
    });

    // 5. Modul Keuangan & Pembayaran
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::middleware('permission:finance.payment')->group(function () {
            Route::post('/invoices/{invoice}/payments', [FinanceController::class, 'storePayment'])->name('invoices.payments.store')->whereNumber('invoice');
            Route::post('/invoices/{invoice}/payment', [FinanceController::class, 'storePayment'])->name('payments.store')->whereNumber('invoice');
        });

        Route::middleware('permission:finance.cancel')->group(function () {
            Route::patch('/payments/{payment}/cancel', [FinanceController::class, 'cancelPayment'])->name('payments.cancel')->whereNumber('payment');
        });

        Route::middleware('permission:finance.expense')->group(function () {
            Route::post('/expenses', [FinanceController::class, 'storeExpense'])->name('expenses.store');
        });

        Route::middleware('permission:finance.refund')->group(function () {
            Route::post('/refunds', [FinanceController::class, 'storeRefund'])->name('refunds.store');
            Route::patch('/refunds/{refund}/approve', [FinanceController::class, 'approveRefund'])->name('refunds.approve')->whereNumber('refund');
        });

        Route::middleware('permission:finance.view')->group(function () {
            Route::get('/summary', [FinanceController::class, 'summary'])->name('summary');
            Route::get('/invoices', [FinanceController::class, 'invoices'])->name('invoices');
            Route::get('/invoices-list', [FinanceController::class, 'invoices'])->name('invoices.index');
            Route::get('/invoices/{invoice}', [FinanceController::class, 'showInvoice'])->name('invoices.show')->whereNumber('invoice');
            Route::get('/payments', [FinanceController::class, 'payments'])->name('payments');
            Route::get('/payments-list', [FinanceController::class, 'payments'])->name('payments.index');
            Route::get('/payments/{payment}/receipt-pdf', [FinanceController::class, 'printReceipt'])->name('receipt')->whereNumber('payment');
            Route::get('/payments/{payment}/receipt', [FinanceController::class, 'printReceipt'])->name('payments.receipt-pdf')->whereNumber('payment');
            Route::get('/expenses', [FinanceController::class, 'expenses'])->name('expenses');
            Route::get('/expenses-list', [FinanceController::class, 'expenses'])->name('expenses.index');
            Route::get('/refunds', [FinanceController::class, 'refunds'])->name('refunds');
            Route::get('/refunds-list', [FinanceController::class, 'refunds'])->name('refunds.index');
        });
    });

    // 6. Modul Laporan
    Route::prefix('reports')->name('reports.')->middleware('permission:report.view')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/payments', [ReportController::class, 'payments'])->name('payments');
        Route::get('/receivables', [ReportController::class, 'receivables'])->name('receivables');
        Route::get('/profit-loss', [ReportController::class, 'profitLoss'])->name('profit-loss');
        Route::get('/departures', [ReportController::class, 'departures'])->name('departures');
    });

    // 7. Modul User & Role
    Route::middleware('permission:role.manage')->group(function () {
        Route::get('/roles', [UserController::class, 'roles'])->name('users.roles');
        Route::post('/roles/{role}/permissions', [UserController::class, 'updateRolePermissions'])->name('users.roles.update')->whereNumber('role');
        Route::put('/roles/{role}/permissions', [UserController::class, 'updateRolePermissions'])->name('roles.update')->whereNumber('role');
    });

    // Fitur Create New Admin Account (Hanya Admin & Super Admin)
    Route::middleware('role:Admin|Super Admin')->group(function () {
        Route::get('/users/create-admin', [UserController::class, 'createAdmin'])->name('users.create-admin');
        Route::post('/users/create-admin', [UserController::class, 'storeAdmin'])->name('users.store-admin');
    });

    Route::middleware('permission:user.create')->group(function () {
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
    });

    Route::middleware('permission:user.edit')->group(function () {
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit')->whereNumber('user');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update')->whereNumber('user');
        Route::patch('/users/{user}', [UserController::class, 'update'])->whereNumber('user');
        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status')->whereNumber('user');
    });

    Route::middleware('permission:user.delete')->group(function () {
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy')->whereNumber('user');
    });

    Route::middleware('permission:user.view')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show')->whereNumber('user');
    });

    // 8. Modul WhatsApp
    Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
        Route::middleware('permission:whatsapp.send')->group(function () {
            Route::post('/send', [WhatsAppController::class, 'sendMessage'])->name('send');
        });
        Route::middleware('permission:whatsapp.template')->group(function () {
            Route::post('/templates', [WhatsAppController::class, 'storeTemplate'])->name('templates.store');
            Route::put('/templates/{template}', [WhatsAppController::class, 'updateTemplate'])->name('templates.update')->whereNumber('template');
        });
        Route::middleware('permission:whatsapp.view')->group(function () {
            Route::get('/', [WhatsAppController::class, 'index'])->name('index');
            Route::get('/templates', [WhatsAppController::class, 'templates'])->name('templates');
            Route::get('/templates-list', [WhatsAppController::class, 'templates'])->name('templates.index');
        });
    });

    // 9. Modul Notifikasi & Audit Log
    Route::middleware('permission:notification.view')->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read')->whereNumber('notification');
        Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read.patch')->whereNumber('notification');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    });

    Route::middleware('permission:audit.view')->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/audit-logs/{log}', [AuditLogController::class, 'show'])->name('audit-logs.show')->whereNumber('log');
    });

    // 10. Modul Pengaturan
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::middleware('permission:setting.edit')->group(function () {
            Route::put('/company', [SettingController::class, 'updateCompany'])->name('update');
            Route::put('/company-profile', [SettingController::class, 'updateCompany'])->name('company.update');
            Route::post('/banks', [SettingController::class, 'storeBankAccount'])->name('banks.store');
            Route::put('/banks/{bankAccount}', [SettingController::class, 'updateBankAccount'])->name('banks.update')->whereNumber('bankAccount');
            Route::delete('/banks/{bankAccount}', [SettingController::class, 'deleteBankAccount'])->name('banks.delete')->whereNumber('bankAccount');
        });
        Route::middleware('permission:setting.view')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('index');
        });
    });
});
