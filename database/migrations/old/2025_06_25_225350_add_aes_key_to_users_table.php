<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('aes_key', 32)
                  ->nullable()
                  ->after('phone_no')
                  ->comment('AES-128 encryption key (16 bytes stored as 32 hex characters)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
             Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('aes_key');
            });
        });
    }
};
