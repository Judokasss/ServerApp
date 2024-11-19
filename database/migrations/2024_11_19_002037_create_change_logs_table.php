<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('change_logs', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type')->comment('Тип сущности');
            $table->unsignedBigInteger('entity_id')->comment('ID мутируемой записи сущности');
            $table->jsonb('before')->comment('Состояние записи до изменений');
            $table->jsonb('after')->comment('Состояние записи после изменений');
            $table->timestamp('created_at')->useCurrent()->comment('Время создания записи в журнале');
            $table->unsignedBigInteger('created_by')->comment('ID пользователя, совершившего изменение');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade')->comment('Связь с таблицей users, каскадное удаление');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('change_logs');
    }
};
