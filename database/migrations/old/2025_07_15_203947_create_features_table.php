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
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->unique();
            $table->string('bank_logo')->default(false);
            $table->boolean('chip')->default(false);
            $table->boolean('mag_strip')->default(false);
            $table->boolean('sig_strip')->default(false);
            $table->boolean('hologram')->default(false);
            $table->boolean('customer_service')->default(false);
            $table->boolean('symmetry')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('features');
    }
};
