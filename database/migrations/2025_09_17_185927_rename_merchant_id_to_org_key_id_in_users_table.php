<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('merchant_id', 'org_key_id');
            $table->renameColumn('business_verified', 'organization_verified');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('org_key_id', 'merchant_id');
            $table->renameColumn('organization_verified', 'business_verified');
        });
    }
};