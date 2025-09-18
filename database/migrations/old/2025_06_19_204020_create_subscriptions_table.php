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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('merchant_id');
            $table->unsignedBigInteger('package_id')->nullable();
            $table->integer('api_calls_used')->default(0);       // total hits in current cycle
            $table->integer('overage_calls')->default(0);        // extra hits beyond limit
            $table->date('renewal_date');                        // monthly billing reset date
            $table->boolean('is_blocked')->default(false);       // block after due date if not paid
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
