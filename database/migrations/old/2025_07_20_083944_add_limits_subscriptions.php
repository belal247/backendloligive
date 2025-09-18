<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->integer('api_calls_limit')->nullable()->after('package_id');
        });

        Schema::table('old_subscriptions', function (Blueprint $table) {
            $table->integer('api_calls_limit')->nullable()->after('package_id');
        });
    }

    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('api_calls_limit');
        });

        Schema::table('old_subscriptions', function (Blueprint $table) {
            $table->dropColumn('api_calls_limit');
        });
    }
};
