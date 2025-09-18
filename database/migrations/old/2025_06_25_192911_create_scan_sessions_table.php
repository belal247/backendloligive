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
        Schema::create('scan_sessions', function (Blueprint $table) {
        $table->id(); // primary key
        $table->uuid('scan_id')->unique();                // UUID used in API/URL
        $table->string('merchant_id');                    // FK from users.merchant_id
        $table->string('merchant_contact')->nullable();               // email or phone
        $table->string('isMobile')->nullable();                       // device type
        $table->integer('tries')->default(0);             // number of scan attempts
        $table->string('encryption_key')->nullable();                 // merchant-specific encryption key
        $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scan_sessions');
    }
};
