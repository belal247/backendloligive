<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payment_details', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('country');
            $table->json('payment_result')->nullable()->after('payment_method');
        });
    }

    public function down()
    {
        Schema::table('payment_details', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_result']);
        });
    }
};