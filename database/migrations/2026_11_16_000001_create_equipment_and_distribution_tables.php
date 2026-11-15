<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Kategori Perlengkapan
        Schema::create('equipment_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Master Perlengkapan / Barang
        Schema::create('equipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('equipment_categories')->nullOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('unit')->default('pcs'); // pcs, pasang, set, buah, dll.
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(5); // Ambang batas peringatan stok menipis
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Riwayat Mutasi Stok Barang
        Schema::create('equipment_mutations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipments')->cascadeOnDelete();
            $table->string('type'); // in (masuk), out (keluar/distribusi), damaged (rusak), adjustment (penyesuaian)
            $table->integer('quantity');
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->string('reference_type')->nullable(); // distribution, restock, damage_report, adjustment
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['type', 'created_at']);
        });

        // 4. Transaksi Distribusi Perlengkapan ke Jamaah
        Schema::create('equipment_distributions', function (Blueprint $table) {
            $table->id();
            $table->string('distribution_number')->unique(); // DST-YYYYMM-XXXX
            $table->foreignId('jamaah_id')->constrained('jamaahs')->cascadeOnDelete();
            $table->foreignId('departure_id')->nullable()->constrained('departures')->nullOnDelete();
            $table->date('distribution_date');
            $table->string('status')->default('diserahkan'); // diserahkan, dibatalkan
            $table->string('received_by')->nullable(); // Nama penerima jika diwakilkan
            $table->text('notes')->nullable();
            $table->foreignId('distributed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['distribution_date', 'status']);
        });

        // 5. Rincian Barang Distribusi
        Schema::create('equipment_distribution_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribution_id')->constrained('equipment_distributions')->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained('equipments')->restrictOnDelete();
            $table->integer('quantity');
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_distribution_items');
        Schema::dropIfExists('equipment_distributions');
        Schema::dropIfExists('equipment_mutations');
        Schema::dropIfExists('equipments');
        Schema::dropIfExists('equipment_categories');
    }
};
