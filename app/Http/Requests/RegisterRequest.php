<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Resources\RegisterResource;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{
    // Пишем правила
    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'min:7', // Минимальная длина 7 символов
                'alpha', // Только буквы
                'regex:/^[A-Z]/', // Начинается с большой буквы
                'unique:users,username', // Уникальность в таблице users
            ],
            'email' => [
                'required',
                'string',
                'email', // Проверка на корректный email
                'unique:users,email', // Уникальность в таблице users
            ],
            'password' => [
                'required',
                'string',
                'min:8', // Минимальная длина 8 символов
                'regex:/[0-9]/', // Содержит хотя бы 1 цифру
                'regex:/[A-Z]/', // Содержит хотя бы 1 символ в верхнем регистре
                'regex:/[a-z]/', // Содержит хотя бы 1 символ в нижнем регистре
                'regex:/[!@#$%^&*(),.?":{}|<>]/', // Содержит хотя бы 1 специальный символ
            ],
            'c_password' => [
                'required',
                'same:password', // Должен совпадать с полем password
            ],
            'birthday' => [
                'required',
                'date',
                'date_format:Y-m-d', // Формат даты: 2000-12-31
            ],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        // Генерация ответа с ошибками валидации
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'message' => 'Error validation',
                'errors' => $validator->errors(),
            ], 422)
        );
    }

    public function toResource()
    {
        return new RegisterResource($this->validated());
    }
}
