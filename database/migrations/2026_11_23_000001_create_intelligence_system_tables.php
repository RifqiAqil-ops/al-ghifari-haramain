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
        // 1. Smart Insights & Alerts
        Schema::create('smart_insights', function (Blueprint $table) {
            $table->id();
            $table->enum('category', ['financial', 'departure', 'document', 'equipment', 'operational'])->default('operational');
            $table->enum('level', ['critical', 'warning', 'info', 'success'])->default('info');
            $table->string('title');
            $table->text('summary');
            $table->json('details')->nullable();
            $table->string('action_text')->nullable();
            $table->string('action_url')->nullable();
            $table->string('source_entity_type')->nullable(); // departure, invoice, jamaah, equipment
            $table->unsignedBigInteger('source_entity_id')->nullable();
            $table->enum('status', ['active', 'acknowledged', 'resolved', 'dismissed'])->default('active');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution_note')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['category', 'status']);
            $table->index(['level', 'status']);
            $table->index(['source_entity_type', 'source_entity_id']);
        });

        // 2. AI Conversations
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title')->default('Percakapan Baru');
            $table->timestamps();

            $table->index('user_id');
        });

        // 3. AI Messages
        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('ai_conversations')->cascadeOnDelete();
            $table->enum('role', ['user', 'assistant', 'system'])->default('user');
            $table->longText('content');
            $table->json('grounding_data')->nullable(); // Data points, source tables, calculation period
            $table->integer('tokens_used')->default(0);
            $table->string('provider')->default('local_heuristic'); // local_heuristic, gemini
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
        });

        // 4. Automation Rules
        Schema::create('automation_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('category', ['billing', 'document', 'departure', 'equipment', 'general'])->default('general');
            $table->string('trigger_type'); // schedule_daily, schedule_hourly, event_registration, event_payment
            $table->json('conditions')->nullable();
            $table->string('action_type'); // create_alert, draft_whatsapp, send_whatsapp, create_notification
            $table->json('action_payload')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_autonomous')->default(false); // false = requires human approval; true = automated
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();

            $table->index(['category', 'is_active']);
        });

        // 5. Automation Execution Logs
        Schema::create('automation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_id')->nullable()->constrained('automation_rules')->nullOnDelete();
            $table->string('trigger_source')->default('scheduler'); // scheduler, event, manual
            $table->string('action_taken');
            $table->enum('status', ['success', 'failed', 'skipped', 'pending_approval'])->default('success');
            $table->string('target_type')->nullable(); // jamaah, invoice, departure
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('idempotency_key')->nullable()->unique();
            $table->json('result_data')->nullable();
            $table->text('error_message')->nullable();
            $table->foreignId('executed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        // 6. Document OCR Logs (Human-in-the-Loop Verification)
        Schema::create('document_ocr_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jamaah_id')->constrained('jamaahs')->cascadeOnDelete();
            $table->foreignId('document_id')->nullable()->constrained('jamaah_documents')->nullOnDelete();
            $table->string('document_type'); // ktp, paspor, kk
            $table->json('extracted_data'); // {nik, name, birth_date, birth_place, passport_number, expiry_date}
            $table->decimal('confidence_score', 5, 2)->default(90.00);
            $table->enum('validation_status', ['valid', 'warning', 'invalid'])->default('valid');
            $table->text('validation_notes')->nullable();
            $table->enum('verification_status', ['pending_verification', 'approved', 'rejected'])->default('pending_verification');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('verification_note')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->boolean('applied_to_jamaah')->default(false);
            $table->timestamps();

            $table->index(['jamaah_id', 'verification_status']);
            $table->index('verification_status');
        });

        // 7. Predictive Snapshots
        Schema::create('predictive_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('metric_type'); // jamaah_growth, occupancy_forecast, cashflow_forecast, equipment_forecast
            $table->date('period_date');
            $table->decimal('projected_value', 15, 2);
            $table->decimal('actual_value', 15, 2)->nullable();
            $table->decimal('confidence_score', 5, 2)->default(80.00);
            $table->json('parameters')->nullable();
            $table->text('limitations_note')->nullable();
            $table->timestamps();

            $table->index(['metric_type', 'period_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predictive_snapshots');
        Schema::dropIfExists('document_ocr_logs');
        Schema::dropIfExists('automation_logs');
        Schema::dropIfExists('automation_rules');
        Schema::dropIfExists('ai_messages');
        Schema::dropIfExists('ai_conversations');
        Schema::dropIfExists('smart_insights');
    }
};
