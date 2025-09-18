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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('country_code', 5); // e.g. +1, +91
            $table->string('phone_no', 20); // phone number without country code
            
            // New verification columns
            $table->boolean('otp_verified')->default(false);
            $table->boolean('business_verified')->default(false);
            
            $table->timestamps();
            
            // Composite unique index for country_code + phone_no
            $table->unique(['country_code', 'phone_no']);
        });

        // Create temp_otp table
        Schema::create('temp_otps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->integer('otp')->unsigned(); // 6-digit OTP
            $table->timestamp('otp_expiry_time');
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temp_otps');
        Schema::dropIfExists('users');
    }
};