<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\AuthResource;
use App\Http\Resources\RegisterResource;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use App\Models\UserToken;
use Illuminate\Support\Facades\Hash;
use App\Services\TokenService;
use Illuminate\Http\Request;
use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Services\TwoFactorService;

class AuthController extends Controller
{
    protected $tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    // Регистрация пользователя
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'birthday' => $request->birthday,
        ]);

        // Назначаем пользователю роль 'user'
        $userRole = Role::where('code', 'USER')->first(); // Находим роль 'USER' по её коду
        if ($userRole) {
            $user->roles()->attach($userRole->id, ['created_by' => $user->id]);
        }

        return response()->json(new RegisterResource($user), 201);
    }

    // Вход пользователя
    public function login(LoginRequest $request, TwoFactorService $service)
    {
        // Проверка пользователя и пароля
        $user = $this->authenticateUser($request->username, $request->password);

        if (!$user) {
            return response()->json([
                'message' => 'Invalid username or password.',
            ], 401);
        }

        // Если 2FA включён
        if ($user->is_two_fa_enabled) {
            // Генерация кода 2FA
            $deviceId = $request->header('Device-ID');
            $code = $service->setCode($user, $deviceId);

            // Отправляем код (например, на email)
            // Mail::to($user->email)->send(new TwoFactorCodeMail($code));

            return response()->json([
                'message' => '2FA code sent.',
                'requires_2fa' => true
            ], 200);
        }

        // Если 2FA не включён, сразу выдаём токен
        $tokens = $this->tokenService->generateToken($user);

        // Возвращаем токены и данные пользователя
        return response()->json([
            'status' => 'success',
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'],
            'user' => new AuthResource($user),
        ], 200);
    }

    // Метод для аутентификации пользователя
    protected function authenticateUser($username, $password)
    {
        $user = User::where('username', $username)->first();

        if ($user && Hash::check($password, $user->password)) {
            return $user;
        }

        return null;
    }

    public function me(Request $request)
    {
        // Извлечение пользователя, который был добавлен в middleware
        $user = $request->user;
        // Возвращение информации о пользователе через ресурс UserResource
        return new UserResource($user);
    }


    // Метод для выхода (удаление текущего токена)
    public function logout(Request $request)
    {
        $token = $request->bearerToken();

        UserToken::where('token', $token)->delete();

        return response()->json(['message' => 'Logged out'], 200);
    }

    // Метод для выхода со всех устройств (удаление всех токенов пользователя)
    public function logoutAll(Request $request)
    {
        $user = $request->user;

        UserToken::where('user_id', $user->id)->delete();

        return response()->json(['message' => 'All tokens deleted'], 200);
    }

    // Получение списка токенов пользователя
    public function tokens(Request $request)
    {
        $userID = $request->user->id;
        $tokens = UserToken::where('user_id', $userID)->get();

        return response()->json($tokens);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = $request->user;

        // Проверяем, соответствует ли текущий пароль
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'The current password is incorrect.',
            ], 400);
        }

        // Проверяем, не совпадает ли новый пароль с текущим
        if (Hash::check($request->new_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'The new password must not match the current password.',
            ], 400);
        }

        // Обновляем пароль
        $user->password = Hash::make($request->new_password);
        $user->save();

        // Удаляем все токены пользователя
        UserToken::where('user_id', $user->id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'The password has been successfully updated.',
        ], 200);
    }

    public function refresh(Request $request)
    {
        $refreshToken = $request->input('refresh_token');

        if (!$refreshToken) {
            return response()->json([
                'message' => 'The Refresh token has not been provided.'
            ], 400);
        }

        try {
            $tokens = $this->tokenService->refreshAccessToken($refreshToken);

            return response()->json([
                'status' => 'success',
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'],
                'expires_at' => $tokens['expires_at'],
            ], 200);
        } catch (HttpResponseException $e) {
            return $e->getResponse();
        }
    }

    public function updateProfile(Request $request)
    {
        // Получаем текущего пользователя
        $user = $request->user();

        // Проверяем наличие разрешения 'UPDATE_USER'
        if (!$user->hasPermission('UPDATE_USER')) {
            return response()->json([
                'error' => 'У вас нет доступа к этой операции. Необходимое разрешение: UPDATE_USER'
            ], 403);
        }

        // Валидация входных данных
        $request->validate([
            'username' => 'string|max:255|alpha|regex:/^[A-Z]/',
            'email' => 'string|email|max:255|unique:users,email,' . $user->id,
            'birthday' => 'date|nullable',
        ]);

        // Обновляем данные пользователя
        $user->update($request->only(['username', 'email', 'birthday']));

        // Возвращаем обновленную информацию о пользователе
        return new UserResource($user);
    }
    // Запрос нового кода
    public function requestTwoFactorCode(Request $request, TwoFactorService $service)
    {
        $user = $request->user();

        if (!$user->is_two_fa_enabled) { // Переименовано поле
            return response()->json(['message' => '2FA is not enabled'], 400);
        }

        $deviceId = $request->header('Device-ID'); // Получаем идентификатор устройства
        $code = $service->setCode($user, $deviceId);

        // Отправьте код пользователю (например, по электронной почте)
        // Mail::to($user->email)->send(new TwoFactorCodeMail($code));

        return response()->json(['message' => 'New 2FA code sent.']);
    }

    // Подтверждение кода
    public function confirmTwoFactorCode(Request $request, TwoFactorService $service)
    {
        $user = $request->user();
        $code = $request->input('code');
        $deviceId = $request->header('Device-ID');

        if (!$user->is_two_fa_enabled || $user->two_fa_device_id !== $deviceId) { // Проверка на правильное устройство
            return response()->json(['message' => 'Invalid device or 2FA not enabled'], 400);
        }

        if (!$service->isValid($code, $user)) {
            return response()->json(['message' => 'Invalid or expired code'], 400);
        }

        $service->clearCode($user);

        return response()->json(['message' => '2FA verified successfully']);
    }
    // Включение/выключение 2FA
    public function toggleTwoFactor(Request $request)
    {
        $user = $request->user();
        $currentPassword = $request->input('password');

        if (!Hash::check($currentPassword, $user->password)) {
            return response()->json(['message' => 'Invalid password'], 400);
        }

        $user->is_two_fa_enabled = !$user->is_two_fa_enabled; // Переименовано поле
        $user->save();

        return response()->json([
            'message' => $user->is_two_fa_enabled ? '2FA enabled' : '2FA disabled',
        ]);
    }

    public function confirmLogin(Request $request, TwoFactorService $service)
    {
        $user = User::where('email', $request->input('email'))->first();

        // Если пользователь не найден или не включён 2FA
        if (!$user || !$user->is_two_fa_enabled) {
            return response()->json(['message' => 'Invalid request or 2FA not enabled.'], 400);
        }

        $code = $request->input('code');
        $deviceId = $request->header('Device-ID');

        // Проверяем код 2FA
        if (!$service->isValid($code, $user) || ($user->two_fa_device_id && $user->two_fa_device_id !== $deviceId)) {
            return response()->json(['message' => 'Invalid or expired code.'], 400);
        }

        // Очищаем код 2FA
        $service->clearCode($user);

        // Выдаём токен
        $tokens = $this->tokenService->generateToken($user);

        return response()->json([
            'status' => 'success',
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'],
            'user' => new AuthResource($user),
        ], 200);
    }
}
