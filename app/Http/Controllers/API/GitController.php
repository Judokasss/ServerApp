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

        $secretKey = env('GIT_SECRET_KEY'); // Получаем ключ из .env
        $inputSecretKey = $request->input('secret_key'); // Получаем ключ из запроса

        // Проверка наличия ключа в .env
        if (empty($secretKey)) {
            return response()->json(['message' => 'Server secret key is not configured.'], 500);
        }

        // Проверка наличия ключа в запросе
        if (empty($inputSecretKey)) {
            return response()->json(['message' => 'Request secret key is missing.'], 400);
        }

        // Сравнение ключей
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
            $resetChanges = $this->executeCommand("reset --hard", $projectPath);
            $branchSwitch = $this->executeCommand("checkout master", $projectPath);
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
        } finally {
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
        // $statusCode: код возврата команды (0 — успех, другое значение — ошибка).

        // Проверяем статус выполнения
        if ($statusCode !== 0) {
            throw new \Exception(join(" ", $output));
        }

        return join(" ", $output); // Возвращаем результат выполнения
    }
}
