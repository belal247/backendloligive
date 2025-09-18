<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ach_payment_info', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('merchant_id');
            $table->text('ach_string');
            $table->timestamps();
            
            // Add index for better performance
            $table->index('merchant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ach_payment_info');
    }
};