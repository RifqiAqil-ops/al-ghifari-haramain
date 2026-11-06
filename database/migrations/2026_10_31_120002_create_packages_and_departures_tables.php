<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('type', ['umrah', 'haji']);
            $table->string('program_category')->nullable(); // Reguler, Plus, VIP, Furoda, Khusus
            $table->year('haji_year')->nullable();
            $table->decimal('price', 15, 2);
            $table->decimal('quad_price', 15, 2)->nullable();
            $table->decimal('triple_price', 15, 2)->nullable();
            $table->decimal('double_price', 15, 2)->nullable();
            $table->integer('duration_days')->default(9);
            $table->integer('quota')->default(45);
            $table->string('airline')->nullable();
            $table->string('hotel_makkah')->nullable();
            $table->tinyInteger('hotel_makkah_rating')->default(5);
            $table->string('hotel_madinah')->nullable();
            $table->tinyInteger('hotel_madinah_rating')->default(5);
            $table->date('start_period')->nullable();
            $table->date('end_period')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_promo')->default(false);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->enum('status', ['draft', 'aktif', 'penuh', 'selesai', 'arsip'])->default('aktif');
            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('status');
        });

        Schema::create('package_facilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_included')->default(true);
            $table->timestamps();
        });

        Schema::create('departures', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->date('departure_date');
            $table->date('return_date');
            $table->string('origin_airport')->nullable()->default('CGK - Bandara Soekarno-Hatta');
            $table->string('destination_airport')->nullable()->default('JED - King Abdulaziz Jeddah');
            $table->string('airline')->nullable();
            $table->string('flight_number')->nullable();
            $table->time('departure_time')->nullable();
            $table->time('arrival_time')->nullable();
            $table->integer('quota')->default(45);
            $table->string('leader_name')->nullable();
            $table->string('leader_phone')->nullable();
            $table->enum('status', [
                'persiapan',
                'siap_berangkat',
                'berangkat',
                'dalam_perjalanan',
                'pulang',
                'selesai',
                'dibatalkan'
            ])->default('persiapan');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('departure_date');
            $table->index('status');
        });

        Schema::create('flights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departure_id')->constrained('departures')->cascadeOnDelete();
            $table->enum('flight_type', ['keberangkatan', 'kepulangan', 'transit'])->default('keberangkatan');
            $table->string('airline');
            $table->string('flight_number');
            $table->string('departure_airport');
            $table->string('arrival_airport');
            $table->dateTime('departure_time');
            $table->dateTime('arrival_time');
            $table->string('terminal')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('accommodations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departure_id')->constrained('departures')->cascadeOnDelete();
            $table->string('city'); // Makkah, Madinah, Transit
            $table->string('hotel_name');
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->text('address')->nullable();
            $table->string('contact_phone')->nullable();
            $table->timestamps();
        });

        Schema::create('itineraries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departure_id')->constrained('departures')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('itinerary_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('itinerary_id')->constrained('itineraries')->cascadeOnDelete();
            $table->integer('day_number');
            $table->date('date')->nullable();
            $table->string('time')->nullable();
            $table->string('title');
            $table->text('activity');
            $table->string('location')->nullable();
            $table->timestamps();
        });

        Schema::create('departure_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departure_id')->constrained('departures')->cascadeOnDelete();
            $table->string('item_name');
            $table->string('category')->default('dokumen'); // dokumen, logistik, visa, tiket, hotel, perlengkapan
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departure_checklists');
        Schema::dropIfExists('itinerary_items');
        Schema::dropIfExists('itineraries');
        Schema::dropIfExists('accommodations');
        Schema::dropIfExists('flights');
        Schema::dropIfExists('departures');
        Schema::dropIfExists('package_facilities');
        Schema::dropIfExists('packages');
    }
};
