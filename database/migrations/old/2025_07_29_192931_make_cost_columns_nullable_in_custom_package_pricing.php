<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('custom_package_pricing', function (Blueprint $table) {
            $table->decimal('business_user_per_custom_api_cost', 10, 2)
                  ->nullable()
                  ->change();
                  
            $table->decimal('enterprise_user_per_custom_api_cost', 10, 2)
                  ->nullable()
                  ->change();
        });
    }

    public function down()
    {
        Schema::table('custom_package_pricing', function (Blueprint $table) {
            $table->decimal('business_user_per_custom_api_cost', 10, 2)
                  ->nullable(false)
                  ->change();
                  
            $table->decimal('enterprise_user_per_custom_api_cost', 10, 2)
                  ->nullable(false)
                  ->change();
        });
    }
};