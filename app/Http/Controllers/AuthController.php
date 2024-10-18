<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\AuthResource;
use App\Http\Resources\RegisterResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserToken;
use Illuminate\Support\Facades\Hash;
use App\Services\TokenService;
use Illuminate\Http\Request;
use App\Http\Requests\ChangePasswordRequest;

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

        return response()->json(new RegisterResource($user), 201);
    }

    // Вход пользователя
    public function login(LoginRequest $request)
    {
        // Проверка пользователя и пароля
        $user = $this->authenticateUser($request->username, $request->password);

        if (!$user) {
            return response()->json([
                'message' => 'Неверное имя пользователя или пароль.',
            ], 401);
        }

        // Генерация токена через сервис
        $token = $this->tokenService->generateToken($user);

        // Возвращаем токен и данные пользователя
        return response()->json([
            'status' => 'success',
            'token' => $token,
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
                'message' => 'Текущий пароль неверен.',
            ], 400);
        }

        // Проверяем, не совпадает ли новый пароль с текущим
        if (Hash::check($request->new_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Новый пароль не должен совпадать с текущим паролем.',
            ], 400);
        }

        // Обновляем пароль
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Пароль успешно обновлен.',
        ], 200);
    }
}
