<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Resources\AuthResource;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
{

    //@return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'min:7', // Минимальная длина 7 символов
                'alpha', // Только буквы
                'regex:/^[A-Z].*/' // Начинается с большой буквы
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


    //Получить ресурс для успешной авторизации.
    //@return AuthResource
    public function toResource()
    {
        return new AuthResource($this->validated());
    }
}
