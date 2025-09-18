<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('custom_package_pricing', function (Blueprint $table) {
            $table->decimal('enterprise_user_per_custom_api_cost', 10, 2)->nullable()->after('business_user_per_custom_api_cost');
            $table->decimal('sub_business_fee', 10, 2)->nullable()->after('enterprise_user_per_custom_api_cost');
            $table->string('billing_cycle')->nullable()->after('sub_business_fee');
            
            // Also modify the existing field to be nullable if it wasn't already
            $table->decimal('business_user_per_custom_api_cost', 10, 2)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('custom_package_pricing', function (Blueprint $table) {
            $table->dropColumn([
                'enterprise_user_per_custom_api_cost',
                'sub_business_fee',
                'billing_cycle'
            ]);
            
            // If you want to revert the nullable change (optional)
            $table->decimal('business_user_per_custom_api_cost', 10, 2)->nullable(false)->change();
        });
    }
};