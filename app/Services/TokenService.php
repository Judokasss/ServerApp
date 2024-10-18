<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserToken;
use Illuminate\Http\Exceptions\HttpResponseException;
use Carbon\Carbon;

class TokenService
{
    public function generateToken(User $user)
    {
        // Проверка лимита активных токенов
        $this->checkTokenLimit($user);

        // Генерация access токена
        $accessToken = $this->createToken();

        // Генерация refresh токена
        $refreshToken = $this->createToken();

        // Сохранение токенов
        $this->storeToken($user, $accessToken, $refreshToken);

        // Возвращаем токены
        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ];
    }

    // Метод для проверки лимита активных токенов
    protected function checkTokenLimit(User $user)
    {
        $maxTokens = env('MAX_ACTIVE_TOKENS', 5);
        $activeTokensCount = UserToken::where('user_id', $user->id)->count();

        if ($activeTokensCount >= $maxTokens) {
            throw new HttpResponseException(response()->json([
                'message' => 'The maximum number of active tokens has been exceeded.'
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
    protected function storeToken(User $user,  $accessToken, $refreshToken)
    {
        $accessTokenExpiresAt = Carbon::now()->addMinutes((int)env('TOKEN_LIFETIME', 1));
        $refreshTokenExpiresAt = Carbon::now()->addDays((int)env('REFRESH_TOKEN_LIFETIME', 7));

        UserToken::create([
            'user_id' => $user->id,
            'token' => $accessToken,
            'expires_at' => $accessTokenExpiresAt,
            'refresh_token' => $refreshToken,
            'refresh_expires_at' => $refreshTokenExpiresAt,
        ]);
    }

    // Метод для проверки срока действия токена
    public function isTokenExpired(UserToken $userToken)
    {
        $expiryTime = $userToken->expires_at;
        $currentTime = Carbon::now();

        // Проверяем сравнение времени окончания действия токена с текущим временем
        if ($currentTime->gte($expiryTime)) {
            $userToken->delete();
            return true;
        }

        return false;
    }

    //  Метод для обновления access токена с помощью refresh токена
    public function refreshAccessToken($refreshToken)
    {
        $userToken = UserToken::where('refresh_token', $refreshToken)->first();

        if (!$userToken) {
            throw new HttpResponseException(response()->json([
                'message' => 'Invalid refresh token.'
            ], 401));
        }

        // Проверяем истечение срока действия refresh токена
        if ($this->isRefreshTokenExpired($userToken)) {
            $userToken->delete();
            throw new HttpResponseException(response()->json([
                'message' => 'The refresh token has expired.'
            ], 401));
        }

        // Генерируем новый access токен
        $newAccessToken = $this->createToken();
        $newAccessExpiresAt = Carbon::now()->addMinutes((int)env('TOKEN_LIFETIME', 15));

        // Генерируем новый refresh токен
        $newRefreshToken = $this->createToken();
        $newRefreshExpiresAt = Carbon::now()->addDays((int)env('REFRESH_TOKEN_LIFETIME', 7));

        // Обновляем токены в базе данных
        $userToken->update([
            'token' => $newAccessToken,
            'expires_at' => $newAccessExpiresAt,
            'refresh_token' => $newRefreshToken,
            'refresh_expires_at' => $newRefreshExpiresAt,
        ]);

        return [
            'access_token' => $newAccessToken,
            'refresh_token' => $newRefreshToken,
            'expires_at' => $newAccessExpiresAt,
        ];
    }
    // Метод для проверки истечения срока действия refresh токена
    public function isRefreshTokenExpired(UserToken $userToken)
    {
        $expiryTime = $userToken->refresh_expires_at;
        $currentTime = Carbon::now();

        return $currentTime->gte($expiryTime);
    }
}
