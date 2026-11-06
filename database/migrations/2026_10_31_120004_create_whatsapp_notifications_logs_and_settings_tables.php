<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('category', ['pembayaran', 'dokumen', 'keberangkatan', 'umum'])->default('umum');
            $table->text('body');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jamaah_id')->nullable()->constrained('jamaahs')->nullOnDelete();
            $table->string('phone_number');
            $table->string('message_type')->default('manual');
            $table->text('content');
            $table->enum('status', ['pending', 'sent', 'delivered', 'read', 'failed', 'simulated'])->default('pending');
            $table->json('response_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('phone_number');
            $table->index('status');
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('type'); // pembayaran_jatuh_tempo, dokumen_kurang, keberangkatan_dekat, pendaftaran_baru, wa_gagal
            $table->string('title');
            $table->text('message');
            $table->string('link')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('action'); // CREATE, UPDATE, DELETE, LOGIN, LOGOUT, REVERSAL
            $table->string('module'); // Jamaah, Keuangan, Paket, Keberangkatan, User, Pengaturan
            $table->text('description');
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index('action');
            $table->index('module');
            $table->index('created_at');
        });

        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->default('Al Ghifari Haramain');
            $table->string('brand_tagline')->default('UMROH & HAJI SPECIALIST');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->text('address')->nullable();
            $table->string('website')->nullable();
            $table->string('registration_prefix')->default('JAM');
            $table->string('invoice_prefix')->default('INV');
            $table->string('receipt_prefix')->default('KWT');
            $table->text('receipt_footer_note')->nullable();
            $table->timestamps();
        });

        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name');
            $table->string('account_number');
            $table->string('account_name');
            $table->string('branch')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
        Schema::dropIfExists('company_settings');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('whatsapp_messages');
        Schema::dropIfExists('whatsapp_templates');
    }
};
