<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class GitController extends Controller
{
    public function handleGitWebhook(Request $request)
    {
        $gitBinary = '"C:/Program Files/Git/bin/git.exe"'; // Полный путь к git.exe

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
                // Указание пути к репозиторию
                $repositoryPath = 'D:/OSPanel/home/application'; // Заменить на актуальный путь
                Log::info('Repository path: ' . $repositoryPath);

                // Стэширование изменений перед переключением ветки
                shell_exec("cd {$repositoryPath} && {$gitBinary} git stash");
                Log::info('Stashed local changes before switching branch');

                // Выполняем команду git checkout
                $checkoutOutput = shell_exec("cd {$repositoryPath} && {$gitBinary} git checkout master 2>&1");
                Log::info('Git checkout output: ' . $checkoutOutput);

                // Проверяем текущую ветку
                $branchCheck = shell_exec("cd {$repositoryPath} && {$gitBinary} git rev-parse --abbrev-ref HEAD");
                Log::info('Current branch: ' . trim($branchCheck));

                if (trim($branchCheck) !== 'master') {
                    Log::error("Failed to switch to master branch. Current branch is: " . $branchCheck);
                    return response()->json(['message' => 'Failed to switch to master branch'], 500);
                }

                // 9.3 Отменяем все изменения
                shell_exec("cd {$repositoryPath} && {$gitBinary} git reset --hard");
                Log::info('Resetting local changes');

                // 9.4 Обновляем проект с Git
                $pullOutput = shell_exec("cd {$repositoryPath} && {$gitBinary} git pull origin master 2>&1");
                Log::info('Git pull output: ' . $pullOutput);

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
