<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserToken;
use Illuminate\Http\Exceptions\HttpResponseException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TokenService
{
    public function generateToken(User $user)
    {
        // Проверка лимита активных токенов
        $this->checkTokenLimit($user);

        // Генерация токена
        $token = $this->createToken();

        // Сохранение токена
        $this->storeToken($user, $token);

        return $token;
    }

    // Метод для проверки лимита активных токенов
    protected function checkTokenLimit(User $user)
    {
        $maxTokens = config('auth.max_active_tokens', env('MAX_ACTIVE_TOKENS', 5));
        $activeTokensCount = UserToken::where('user_id', $user->id)->count();

        if ($activeTokensCount >= $maxTokens) {
            throw new HttpResponseException(response()->json([
                'message' => 'Превышено максимальное количество активных токенов.'
            ], 403));
        }
    }

    // Метод для генерации токена
    protected function createToken()
    {
        $bytes = random_bytes(40);
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }

    // Метод для сохранения токена в базе данных
    protected function storeToken(User $user, $token)
    {
        $expiresAt = Carbon::now()->addMinutes((int)env('TOKEN_LIFETIME', 1));

        UserToken::create([
            'user_id' => $user->id,
            'token' => $token,
            'created_at' => Carbon::now(), // Сохраняем время создания токена
            'expires_at' => $expiresAt // Сохраняем время истечения токена
        ]);
    }

    // Метод для проверки срока действия токена
    public function isTokenExpired(UserToken $userToken)
    {
        $expiryTime = $userToken->expires_at;  // Новое поле для времени истечения
        $currentTime = Carbon::now();

        // Проверяем сравнение времени окончания действия токена с текущим временем
        if ($currentTime->gte($expiryTime)) {
            $userToken->delete();
            return true;
        }

        return false;
    }
}
