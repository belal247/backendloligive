<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('subscription_package_price', 10, 2)->nullable()->after('merchant_id');
            $table->decimal('over_limit_price', 10, 2)->nullable()->after('subscription_package_price');
            $table->integer('subscription_amount')->nullable()->after('over_limit_price');
            $table->decimal('price_per_scan', 10, 2)->nullable()->after('subscription_amount');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'subscription_package_price',
                'over_limit_price',
                'subscription_amount',
                'price_per_scan'
            ]);
        });
    }
};