<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number')->unique();
            $table->foreignId('jamaah_id')->constrained('jamaahs')->cascadeOnDelete();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->foreignId('departure_id')->constrained('departures')->cascadeOnDelete();
            $table->date('registration_date');
            $table->enum('room_type', ['quad', 'triple', 'double', 'single'])->default('quad');
            $table->decimal('agreed_price', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('final_price', 15, 2);
            $table->enum('status', ['terdaftar', 'proses', 'siap_berangkat', 'berangkat', 'selesai', 'batal'])->default('terdaftar');
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('special_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['jamaah_id', 'departure_id']);
            $table->index('status');
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('registration_id')->constrained('registrations')->cascadeOnDelete();
            $table->decimal('total_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2)->default(0);
            $table->enum('status', ['belum_bayar', 'sebagian', 'lunas', 'batal'])->default('belum_bayar');
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('due_date');
        });

        Schema::create('transaction_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['pemasukan', 'pengeluaran']);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->string('transaction_number')->unique();
            $table->string('receipt_number')->unique();
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->enum('payment_type', ['dp', 'cicilan', 'pelunasan'])->default('dp');
            $table->enum('payment_method', ['transfer', 'tunai'])->default('transfer');
            $table->string('bank_name')->nullable();
            $table->string('sender_name')->nullable();
            $table->string('proof_file')->nullable();
            $table->enum('status', ['pending', 'valid', 'dibatalkan'])->default('valid');
            $table->text('notes')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('payment_date');
            $table->index('status');
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number')->unique();
            $table->foreignId('category_id')->nullable()->constrained('transaction_categories')->nullOnDelete();
            $table->foreignId('departure_id')->nullable()->constrained('departures')->nullOnDelete();
            $table->string('title');
            $table->date('expense_date');
            $table->decimal('amount', 15, 2);
            $table->enum('payment_method', ['transfer', 'tunai'])->default('transfer');
            $table->string('proof_file')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_cancelled')->default(false);
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('expense_date');
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->string('refund_number')->unique();
            $table->foreignId('registration_id')->constrained('registrations')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->date('refund_date');
            $table->decimal('amount', 15, 2);
            $table->text('reason');
            $table->enum('payment_method', ['transfer', 'tunai'])->default('transfer');
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();
            $table->enum('status', ['diajukan', 'disetujui', 'ditolak', 'selesai'])->default('diajukan');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('transaction_categories');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('registrations');
    }
};
