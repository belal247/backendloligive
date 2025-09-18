<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('business_profiles', function (Blueprint $table) {
            // Business Contact
            $table->string('email')->after('country');
            
            // Account Holder Information
            $table->string('account_holder_email')->after('account_holder_last_name');
            $table->date('account_holder_date_of_birth')->nullable()->after('account_holder_email');
            
            // Account Holder Address
            $table->string('account_holder_street')->after('account_holder_date_of_birth');
            $table->string('account_holder_street_line2')->nullable()->after('account_holder_street');
            $table->string('account_holder_city')->after('account_holder_street_line2');
            $table->string('account_holder_state')->after('account_holder_city');
            $table->string('account_holder_zip_code')->nullable()->after('account_holder_state');
            $table->string('account_holder_country')->default('United States of America')->after('account_holder_zip_code');
            
            // KYC Information
            $table->string('account_holder_id_type')->nullable()->after('account_holder_country');
            $table->string('account_holder_id_number')->nullable()->after('account_holder_id_type');
            $table->string('account_holder_id_document_path')->nullable()->after('account_holder_id_number');
        });
    }

    public function down()
    {
        Schema::table('business_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'email',
                'account_holder_email',
                'account_holder_date_of_birth',
                'account_holder_street',
                'account_holder_street_line2',
                'account_holder_city',
                'account_holder_state',
                'account_holder_zip_code',
                'account_holder_country',
                'account_holder_id_type',
                'account_holder_id_number',
                'account_holder_id_document_path'
            ]);
        });
    }
};