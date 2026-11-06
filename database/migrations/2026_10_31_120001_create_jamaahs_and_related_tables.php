<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jamaahs', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number')->unique();
            $table->string('name');
            $table->string('nik', 16)->unique();
            $table->string('family_card_number', 16)->nullable();
            $table->string('passport_number', 20)->nullable();
            $table->date('passport_expiry_date')->nullable();
            $table->string('passport_issued_place')->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['L', 'P'])->default('L');
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_relation')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('photo')->nullable();
            $table->text('special_needs')->nullable();
            $table->enum('status', ['aktif', 'nonaktif', 'arsip'])->default('aktif');
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('phone');
            $table->index('status');
        });

        Schema::create('jamaah_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jamaah_id')->constrained('jamaahs')->cascadeOnDelete();
            $table->string('document_type'); // ktp, kk, paspor, pas_foto, buku_nikah, lainnya
            $table->string('document_name');
            $table->string('file_path');
            $table->bigInteger('file_size')->nullable();
            $table->string('file_type')->nullable();
            $table->enum('status', ['menunggu_verifikasi', 'diverifikasi', 'ditolak'])->default('menunggu_verifikasi');
            $table->text('verification_note')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['jamaah_id', 'document_type']);
        });

        Schema::create('jamaah_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jamaah_id')->constrained('jamaahs')->cascadeOnDelete();
            $table->string('name');
            $table->string('relationship'); // suami, istri, anak, orang_tua, saudara, kerabat
            $table->string('phone');
            $table->text('address')->nullable();
            $table->boolean('is_emergency_contact')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jamaah_contacts');
        Schema::dropIfExists('jamaah_documents');
        Schema::dropIfExists('jamaahs');
    }
};
