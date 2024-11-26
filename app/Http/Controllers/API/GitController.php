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
        $gitBinary = '"C:/Program Files/Git/bin/git.exe"';
        $repositoryPath = 'D:/OSPanel/home/application';

        $secretKey = env('GIT_SECRET_KEY');

        $inputSecretKey = $request->input('secret_key');

        if ($inputSecretKey !== $secretKey) {

            return response()->json(['message' => 'Invalid secret key.'], 403);
        }

        $lock = Cache::lock('git-update-lock', 30);

        if (!$lock->get()) {
            return response()->json(['message' => 'Another update process is currently running. Please try again later.'], 429);
        }

        try {
            // Логирование даты и IP-адреса
            $ipAddress = $request->ip();
            $currentDate = now()->toDateTimeString();
            Log::info("Git hook triggered", [
                'date' => $currentDate,
                'ip_address' => $ipAddress,
            ]);

            // Выполнение Git-операций
            $projectPath = base_path(); // Путь к проекту
            $branchSwitch = $this->executeCommand("checkout master", $projectPath);
            $resetChanges = $this->executeCommand("reset --hard", $projectPath);
            $pullChanges = $this->executeCommand("pull origin master", $projectPath);

            // Логирование выполнения
            Log::info("Git operations completed", [
                'branch_switch' => $branchSwitch,
                'reset_changes' => $resetChanges,
                'pull_changes' => $pullChanges,
            ]);

            return response()->json([
                'message' => 'Project successfully updated from Git.',
                'logs' => [
                    'branch_switch' => $branchSwitch,
                    'reset_changes' => $resetChanges,
                    'pull_changes' => $pullChanges,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error during Git operations", ['error' => $e->getMessage()]);
            return response()->json(['message' => 'An error occurred during the update process.'], 500);
        } finally { // Код, который выполняется всегда, вне зависимости от ошибок
            // Освобождение блокировки
            $lock->release();
        }
    }

    private function executeCommand(string $command, string $workingDirectory): string
    {
        $gitPath = '"C:\\Program Files\\Git\\cmd\\git.exe"'; // Путь к git.exe

        // Формируем полную команду
        $fullCommand = $gitPath . ' ' . $command;

        // Переключаемся в рабочую директорию и выполняем команду
        chdir($workingDirectory);
        // Исполняем команду
        exec($fullCommand . " 2>&1", $output, $statusCode);
        // 2>&1 перенаправляет стандартный вывод ошибок в стандартный вывод, чтобы ошибки и результат оказались в массиве $output
        // $returnVar: код возврата команды (0 — успех, другое значение — ошибка).

        // Проверяем статус выполнения
        if ($statusCode !== 0) {
            throw new \Exception(join(" ", $output));
        }

        return join(" ", $output); // Возвращаем результат выполнения
    }
}
