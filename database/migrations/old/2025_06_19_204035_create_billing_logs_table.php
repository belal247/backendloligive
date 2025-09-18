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
        Schema::create('billing_logs', function (Blueprint $table) {
            $table->id();
            $table->string('merchant_id');
            $table->date('period_start');
            $table->date('period_end');
            $table->integer('base_calls');
            $table->integer('overage_calls');
            $table->decimal('overage_charge', 8, 2);
            $table->boolean('is_paid')->default(false);
            $table->date('due_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_logs');
    }
};
