<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Change business_verified column type and add verification_reason
        Schema::table('users', function (Blueprint $table) {
            $table->string('business_verified', 20)
                  ->default('PENDING')
                  ->comment('APPROVED, PENDING, INCOMPLETE')
                  ->change();
                  
            $table->text('verification_reason')
                  ->nullable()
                  ->after('business_verified');
        });
    }

    public function down()
    {
        // Revert changes if needed
        Schema::table('users', function (Blueprint $table) {
            // Note: Changing back to integer would lose data, so careful with rollback
            $table->integer('business_verified')
                  ->default(0)
                  ->comment('0 = pending, 1 = approved, 2 = rejected')
                  ->change();
                  
            $table->dropColumn('verification_reason');
        });
    }
};