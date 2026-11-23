<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Hotel Master Table
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('country')->default('Saudi Arabia');
            $table->string('city'); // Makkah, Madinah, Jeddah, etc.
            $table->text('address')->nullable();
            $table->tinyInteger('star_rating')->default(5);
            $table->string('contact_person')->nullable();
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->string('category')->default('Bintang 5'); // Bintang 5, Bintang 4, Butik, Apartemen
            $table->text('contract_info')->nullable();
            $table->text('operational_notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['city', 'is_active']);
        });

        // 2. Hotel Room Types (Dynamic, not hardcoded)
        Schema::create('hotel_room_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->string('name'); // Single, Double, Triple, Quad, Quint, Suite, etc.
            $table->integer('max_occupancy')->default(2);
            $table->text('description')->nullable();
            $table->decimal('default_purchase_rate', 15, 2)->default(0); // Cost in SAR
            $table->decimal('default_selling_rate', 15, 2)->default(0); // Selling in SAR
            $table->string('currency', 3)->default('SAR');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['hotel_id', 'is_active']);
        });

        // 3. Hotel Customers (Travel Agents, Tour Operators, Corporate, Individuals)
        Schema::create('hotel_customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code')->unique();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('country')->default('Indonesia');
            $table->enum('customer_type', ['travel_agency', 'tour_operator', 'corporate', 'individual', 'partner'])->default('travel_agency');
            $table->integer('payment_terms_days')->default(14); // Term of payment
            $table->decimal('credit_limit_idr', 18, 2)->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['customer_type', 'is_active']);
        });

        // 4. Hotel Contracts & Allotments (For Allotment & Reseller models)
        Schema::create('hotel_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->string('contract_number')->unique();
            $table->string('title');
            $table->enum('business_model', ['reseller', 'commission', 'allotment'])->default('reseller');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_contract_value_sar', 18, 2)->default(0);
            $table->integer('allocated_rooms')->default(0);
            $table->decimal('commission_rate', 5, 2)->default(0); // For commission model
            $table->text('cancellation_terms')->nullable();
            $table->enum('status', ['draft', 'aktif', 'selesai', 'dibatalkan'])->default('aktif');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['hotel_id', 'status']);
        });

        // 5. Hotel Exchange Rates (Live, Cached, Snapshots, and History)
        Schema::create('hotel_exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('base_currency', 3)->default('SAR');
            $table->string('target_currency', 3)->default('IDR');
            $table->decimal('rate', 15, 4); // E.g., 4150.2500
            $table->string('provider')->default('ExchangeRate-API'); // ExchangeRate-API, Bank Indonesia, Manual
            $table->string('status', 30)->default('live'); // live, daily_stable, cached, manual, fallback, stale, delayed
            $table->timestamp('fetched_at');
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_manual_override')->default(false);
            $table->text('override_reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['base_currency', 'target_currency', 'fetched_at'], 'h_rates_base_target_idx');
        });

        // 6. Hotel Reservations
        Schema::create('hotel_reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reservation_number')->unique();
            $table->foreignId('customer_id')->constrained('hotel_customers')->cascadeOnDelete();
            $table->foreignId('contract_id')->nullable()->constrained('hotel_contracts')->nullOnDelete();
            $table->enum('business_model', ['reseller', 'commission', 'allotment'])->default('reseller');
            $table->date('booking_date');
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->integer('total_nights')->default(1);
            $table->integer('total_rooms')->default(1);
            $table->integer('total_guests')->default(1);
            
            // Financial amounts (in Primary Currency SAR and Converted Reporting Currency IDR)
            $table->decimal('total_purchase_cost_sar', 18, 2)->default(0);
            $table->decimal('total_selling_price_sar', 18, 2)->default(0);
            $table->decimal('commission_earned_sar', 18, 2)->default(0);
            $table->decimal('gross_profit_sar', 18, 2)->default(0);
            
            $table->decimal('exchange_rate_snapshot', 15, 4)->default(4150.0000); // Snapshot rate
            $table->decimal('total_selling_price_idr', 18, 2)->default(0);
            $table->decimal('total_purchase_cost_idr', 18, 2)->default(0);
            $table->decimal('gross_profit_idr', 18, 2)->default(0);

            $table->enum('status', ['draft', 'pending', 'confirmed', 'partially_paid', 'fully_paid', 'cancelled', 'completed'])->default('draft');
            $table->enum('payment_status', ['belum_bayar', 'sebagian', 'lunas'])->default('belum_bayar');

            $table->string('confirmation_reference')->nullable();
            $table->text('special_requests')->nullable();
            $table->text('internal_notes')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['customer_id', 'status']);
            $table->index(['check_in_date', 'check_out_date']);
        });

        // 7. Hotel Reservation Items (Multi-hotel, multi-room, per-night breakdown)
        Schema::create('hotel_reservation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('hotel_reservations')->cascadeOnDelete();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained('hotel_room_types')->cascadeOnDelete();
            $table->date('check_in');
            $table->date('check_out');
            $table->integer('number_of_nights')->default(1);
            $table->integer('room_quantity')->default(1);
            $table->integer('room_nights')->default(1); // room_quantity * number_of_nights
            
            // Pricing in SAR
            $table->decimal('purchase_rate_per_night_sar', 15, 2)->default(0);
            $table->decimal('selling_rate_per_night_sar', 15, 2)->default(0);
            $table->decimal('subtotal_cost_sar', 18, 2)->default(0);
            $table->decimal('subtotal_selling_sar', 18, 2)->default(0);
            $table->decimal('commission_amount_sar', 18, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 8. Hotel Invoices (Preserves Exchange Rate Snapshot & Reference from Excel)
        Schema::create('hotel_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('reservation_id')->constrained('hotel_reservations')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('hotel_customers')->cascadeOnDelete();
            $table->date('invoice_date');
            $table->date('due_date');
            
            // Invoice Currency (SAR is default operational, with IDR reporting)
            $table->string('currency', 3)->default('SAR');
            $table->decimal('subtotal_amount', 18, 2)->default(0); // Subtotal in invoice currency
            $table->decimal('discount_amount', 18, 2)->default(0);
            $table->decimal('additional_charges', 18, 2)->default(0);
            $table->decimal('total_amount_sar', 18, 2)->default(0);
            
            // Snapshot of exchange rate at invoice creation time
            $table->decimal('exchange_rate_snapshot', 15, 4)->default(4150.0000);
            $table->string('rate_provider_snapshot')->default('ExchangeRate-API');
            $table->timestamp('rate_timestamp_snapshot')->nullable();
            
            // Converted Total in IDR
            $table->decimal('total_amount_idr', 18, 2)->default(0);
            
            // Paid and Remaining Balances in SAR & IDR
            $table->decimal('paid_amount_sar', 18, 2)->default(0);
            $table->decimal('remaining_amount_sar', 18, 2)->default(0);
            $table->decimal('paid_amount_idr', 18, 2)->default(0);
            $table->decimal('remaining_amount_idr', 18, 2)->default(0);

            $table->enum('status', ['draft', 'issued', 'sebagian', 'lunas', 'batal'])->default('issued');
            $table->text('payment_terms')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['customer_id', 'status', 'due_date']);
        });

        // 9. Hotel Invoice Items
        Schema::create('hotel_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('hotel_invoices')->cascadeOnDelete();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->string('hotel_name');
            $table->string('room_type_name');
            $table->date('check_in');
            $table->date('check_out');
            $table->integer('number_of_nights');
            $table->integer('room_quantity');
            $table->integer('room_nights'); // Qty * Nights
            $table->decimal('rate_per_night_sar', 15, 2);
            $table->decimal('subtotal_sar', 18, 2);
            $table->decimal('subtotal_idr', 18, 2);
            $table->timestamps();
        });

        // 10. Hotel Payments (Mixed-Currency: SAR and IDR, Realized FX Gain/Loss)
        Schema::create('hotel_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->foreignId('invoice_id')->constrained('hotel_invoices')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('hotel_customers')->cascadeOnDelete();
            $table->date('payment_date');
            
            // Original Payment Currency & Amount (Could be SAR or IDR)
            $table->string('payment_currency', 3)->default('SAR'); // SAR or IDR
            $table->decimal('payment_amount', 18, 2); // Exact amount paid by customer
            
            // Applied Exchange Rate at the moment of payment
            $table->decimal('payment_exchange_rate', 15, 4); // Rate used for payment
            
            // Calculated Equivalence in Invoice Currency (SAR) & Reporting Currency (IDR)
            $table->decimal('equivalent_amount_sar', 18, 2);
            $table->decimal('equivalent_amount_idr', 18, 2);
            
            // Foreign Exchange Gain / Loss difference against invoice rate snapshot
            $table->decimal('fx_gain_loss_idr', 18, 2)->default(0); // (+) Gain, (-) Loss
            
            $table->enum('payment_type', ['dp', 'cicilan', 'pelunasan', 'kelebihan'])->default('dp');
            $table->string('payment_method')->default('transfer'); // transfer, tunai, etc.
            $table->string('bank_name')->nullable();
            $table->string('sender_name')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('proof_file')->nullable();
            $table->enum('status', ['valid', 'pending', 'dibatalkan'])->default('valid');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['invoice_id', 'payment_date', 'status']);
        });

        // 11. Hotel Operational & Direct Costs
        Schema::create('hotel_costs', function (Blueprint $table) {
            $table->id();
            $table->string('cost_number')->unique();
            $table->foreignId('reservation_id')->nullable()->constrained('hotel_reservations')->nullOnDelete();
            $table->foreignId('hotel_id')->nullable()->constrained('hotels')->nullOnDelete();
            $table->string('supplier_name')->nullable();
            $table->date('cost_date');
            $table->string('title');
            $table->string('cost_type')->default('langsung'); // langsung, penalti, pajak, operasional
            $table->string('currency', 3)->default('SAR');
            $table->decimal('amount_sar', 18, 2)->default(0);
            $table->decimal('exchange_rate', 15, 4)->default(4150.0000);
            $table->decimal('amount_idr', 18, 2)->default(0);
            $table->string('payment_status')->default('lunas'); // belum_bayar, lunas
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // 12. Hotel Audit Logs
        Schema::create('hotel_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // rate_override, invoice_revised, reservation_cancelled, payment_voided
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->text('description');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_audit_logs');
        Schema::dropIfExists('hotel_costs');
        Schema::dropIfExists('hotel_payments');
        Schema::dropIfExists('hotel_invoice_items');
        Schema::dropIfExists('hotel_invoices');
        Schema::dropIfExists('hotel_reservation_items');
        Schema::dropIfExists('hotel_reservations');
        Schema::dropIfExists('hotel_exchange_rates');
        Schema::dropIfExists('hotel_contracts');
        Schema::dropIfExists('hotel_customers');
        Schema::dropIfExists('hotel_room_types');
        Schema::dropIfExists('hotels');
    }
};
