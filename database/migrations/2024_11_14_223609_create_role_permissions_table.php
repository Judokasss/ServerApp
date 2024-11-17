<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade')->comment('Ссылка на роль');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade')->comment('Ссылка на разрешение');

            // Служебные поля
            $table->timestamp('created_at')->useCurrent()->comment('Время создания записи');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->comment('Идентификатор пользователя, создавшего запись');
            $table->softDeletes('deleted_at')->comment('Время мягкого удаления записи');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null')->comment('Идентификатор пользователя, удалившего запись');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
