<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserToken;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Support\Facades\Hash;
use App\Services\TokenService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TwoFactorController extends Controller
{
  protected $twoFactorService; // храним экз сервиса 

  // Внедряем зависимость TwoFactorService, для испо методов в контроллере
  public function __construct(TwoFactorService $twoFactorService)
  {
    $this->twoFactorService = $twoFactorService;
  }

  // Запрос нового 2FA-кода
  public function requestNewCode(Request $request)
  {
    $tempToken = $request->bearerToken();

    // Проверяем временный токен
    $userToken = UserToken::where('token', $tempToken)->where('is_tmp', 1)->first();

    if (!$userToken) {
      return response()->json(['message' => 'Invalid or expired temporary token'], 401);
    }

    // Проверяем срок действия временного токена
    if (now()->greaterThan($userToken->expires_at)) {
      return response()->json(['message' => 'Temporary token expired'], 401);
    }

    $user = $userToken->user;
    $deviceId = $request->header('Device-ID');

    // Параметры ограничения
    $maxRequests = 3;
    $delaySeconds = 30;
    $currentTime = Carbon::now();

    // Логирование текущего состояния
    Log::info('Attempting 2FA Code Request', [
      'user_id' => $user->id,
      'current_time' => $currentTime->toDateTimeString(),
      'code_request_count' => $user->code_request_count,
      'last_request_time' => $user->last_request_time ? $user->last_request_time->toDateTimeString() : null,
    ]);

    // Если прошло больше delaySeconds, сбросить счётчик
    if ($user->last_request_time && ($currentTime->timestamp - $user->last_request_time->timestamp) > $delaySeconds) {
      $user->code_request_count = 0;
      Log::info('Reset code_request_count due to delay', [
        'user_id' => $user->id,
        'code_request_count' => $user->code_request_count,
      ]);
    }

    if ($user->code_request_count >= $maxRequests) {
      if ($user->last_request_time) {
        // Корректный расчёт разницы во времени
        $secondsSinceLastRequest = $currentTime->timestamp - $user->last_request_time->timestamp;

        Log::info('Checking delay', [
          'user_id' => $user->id,
          'seconds_since_last_request' => $secondsSinceLastRequest,
        ]);

        if ($secondsSinceLastRequest < $delaySeconds) {
          $waitTime = $delaySeconds - $secondsSinceLastRequest;

          // Логирование причины отказа
          Log::info('Too many requests', [
            'user_id' => $user->id,
            'seconds_since_last_request' => $secondsSinceLastRequest,
            'wait_time' => $waitTime,
          ]);

          return response()->json([
            'message' => 'Too many requests, please try again ' . ceil($waitTime) . ' second.'
          ], 429);
        } else {
          // Достаточно времени прошло, сбрасываем счётчик
          $user->code_request_count = 0;
          Log::info('Reset code_request_count after delay', [
            'user_id' => $user->id,
            'code_request_count' => $user->code_request_count,
          ]);
        }
      }
    }

    // Разрешаем запрос: увеличиваем счётчик и обновляем время последнего запроса
    $user->code_request_count += 1;
    $user->last_request_time = $currentTime;
    $user->save();

    Log::info('Allowed 2FA Code Request', [
      'user_id' => $user->id,
      'code_request_count' => $user->code_request_count,
      'last_request_time' => $user->last_request_time->toDateTimeString(),
    ]);

    // Генерация и отправка нового 2FA-кода
    $this->twoFactorService->setCode($user, $deviceId);

    return response()->json(['message' => '2FA the code was sent again']);
  }

  // Подтверждение 2FA-кода
  public function confirmCode(Request $request, TokenService $tokenService)
  {
    $tempToken = $request->bearerToken();

    // Проверяем временный токен
    $userToken = UserToken::where('token', $tempToken)->where('is_tmp', 1)->first();

    if (!$userToken) {
      return response()->json(['message' => 'Invalid or expired temporary token'], 401);
    }

    // Проверяем срок действия временного токена
    if (now()->greaterThan($userToken->expires_at)) {
      return response()->json(['message' => 'Temporary token expired'], 401);
    }

    $user = $userToken->user;
    $code = $request->input('code');
    $deviceId = $request->header('Device-ID');

    // Проверяем код 2FA
    if (
      !$user->is_two_fa_enabled ||
      !$this->twoFactorService->isValid($code, $user) ||
      $user->two_fa_device_id !== $deviceId
    ) {
      return response()->json(['message' => 'Invalid or expired 2FA code'], 400);
    }

    // Очищаем 2FA код у пользователя
    $this->twoFactorService->clearCode($user);

    // Сбрасываем счётчик запросов при успешном подтверждении
    $user->code_request_count = 0;
    $user->last_request_time = null;
    $user->save();

    Log::info('2FA Code Confirmed', [
      'user_id' => $user->id,
      'code_request_count' => $user->code_request_count,
      'last_request_time' => $user->last_request_time,
    ]);

    // Удаляем временный токен
    $userToken->delete();

    // Генерируем полноценный токен для пользователя
    $newTokens = $tokenService->generateToken($user);

    return response()->json([
      'status' => 'success',
      'access_token' => $newTokens['access_token'],
      'refresh_token' => $newTokens['refresh_token'],
    ], 200);
  }

  // Включение/выключение 2FA
  public function toggleTwoFactor(Request $request)
  {
    $user = $request->user(); // Инициализация текущего пользователя через Auth
    $currentPassword = $request->input('password');

    // Проверяем правильность текущего пароля
    if (!Hash::check($currentPassword, $user->password)) {
      return response()->json(['message' => 'Invalid password'], 400);
    }

    $user->is_two_fa_enabled = !$user->is_two_fa_enabled; // Переключаем состояние 2FA
    $user->save();

    return response()->json([
      'message' => $user->is_two_fa_enabled ? '2FA enabled' : '2FA disabled',
    ]);
  }
}
