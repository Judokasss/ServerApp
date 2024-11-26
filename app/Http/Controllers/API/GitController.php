<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class GitController extends Controller
{
    public function handleGitWebhook(Request $request)
    {
        // 6. Проверка секретного ключа
        $secretKey = $request->input('secret_key');
        $validKey = env('GIT_SECRET_KEY'); // Получаем секретный ключ из .env

        if ($secretKey !== $validKey) {
            return response()->json(['message' => 'Invalid secret key'], 403);
        }

        // 9. Логирование даты и IP-адреса
        $userIp = $request->ip();
        Log::info("Git hook triggered", [
            'ip' => $userIp,
            'date' => now(),
        ]);

        // 11. Блокировка для выполнения обновления только из одного потока
        $lock = Cache::lock('git-update-lock', 10); // Блокировка на 5 минут

        if ($lock->get()) {
            try {
                // 9.2 Переключаемся на главную ветку
                shell_exec('git checkout master');
                Log::info('Switched to the main branch');

                // 9.3 Отменяем все изменения
                shell_exec('git reset --hard');
                Log::info('Resetting local changes');

                // 9.4 Обновляем проект с Git
                shell_exec('git pull origin master');
                Log::info('Project updated from Git');

                // Сообщение об успешном завершении
                return response()->json(['message' => 'Git update completed successfully']);
            } catch (\Exception $e) {
                Log::error('Error during git update', ['error' => $e->getMessage()]);
                return response()->json(['message' => 'Error during update'], 500);
            } finally {
                // Освобождаем блокировку
                $lock->release();
            }
        } else {
            // 12. Если обновление уже выполняется
            return response()->json(['message' => 'Update is already in progress'], 429);
        }
    }
}
