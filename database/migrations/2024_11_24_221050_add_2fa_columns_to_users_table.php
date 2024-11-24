<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_two_fa_enabled')->default(false);
                $table->string('two_fa_code')->nullable();
                $table->timestamp('two_fa_expires_at')->nullable();
                $table->string('two_fa_device_id')->nullable();
            });
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {});
    }
};
