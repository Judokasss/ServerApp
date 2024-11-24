<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('user_tokens', function (Blueprint $table) {
            $table->boolean('is_tmp')->default(0)->after('refresh_expires_at');
        });
    }

    public function down()
    {
        Schema::table('user_tokens', function (Blueprint $table) {
            $table->dropColumn('is_tmp');
        });
    }
};
