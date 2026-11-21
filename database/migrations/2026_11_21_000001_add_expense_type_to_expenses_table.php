<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->enum('expense_type', ['langsung', 'operasional_umum'])
                ->nullable()
                ->default('langsung')
                ->after('departure_id');
            $table->index(['expense_type', 'departure_id']);
        });

        // Set existing records without departure_id as 'operasional_umum'
        DB::table('expenses')->whereNull('departure_id')->update(['expense_type' => 'operasional_umum']);
        DB::table('expenses')->whereNotNull('departure_id')->update(['expense_type' => 'langsung']);
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex(['expense_type', 'departure_id']);
            $table->dropColumn('expense_type');
        });
    }
};
