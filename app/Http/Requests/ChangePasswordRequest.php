<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ChangePasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'new_password' => [
                'required',
                'string',
                'min:8', // Минимальная длина 8 символов
                'regex:/[0-9]/', // Содержит хотя бы 1 цифру
                'regex:/[A-Z]/', // Содержит хотя бы 1 символ в верхнем регистре
                'regex:/[a-z]/', // Содержит хотя бы 1 символ в нижнем регистре
                'regex:/[!@#$%^&*(),.?":{}|<>]/', // Содержит хотя бы 1 специальный символ
            ],
            'confirm_password' => ['required', 'same:new_password'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        // Генерация ответа с ошибками валидации
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
