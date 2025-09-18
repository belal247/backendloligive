<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ach_payment_info', function (Blueprint $table) {
            $table->string('merchant_id', 255)->change();
        });
    }

    public function down(): void
    {
        Schema::table('ach_payment_info', function (Blueprint $table) {
            $table->unsignedBigInteger('merchant_id')->change();
        });
    }
};