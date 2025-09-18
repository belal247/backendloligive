<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('business_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Business Information
            $table->string('business_name');
            $table->string('business_registration_number');
            
            // Business Address
            $table->string('street');
            $table->string('street_line2')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('zip_code')->nullable();
            $table->string('country');
            
            // Account Holder
            $table->string('account_holder_first_name');
            $table->string('account_holder_last_name');
            
            // Documents
            $table->string('registration_document_path');
            $table->string('bank_info')->nullable(); // In real app, encrypt this!
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('business_profiles');
    }
};