<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('custom_package_pricing', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('package_id');
            $table->decimal('business_user_per_custom_api_cost', 10, 2);
            $table->decimal('enterprise_user_per_custom_api_cost', 10, 2);
            $table->timestamps();
            
            // Add foreign key if packages table exists
            // $table->foreign('package_id')->references('id')->on('packages');
        });
    }

    public function down()
    {
        Schema::dropIfExists('custom_package_pricing');
    }
};
