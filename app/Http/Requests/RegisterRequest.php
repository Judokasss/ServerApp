<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
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
                'min:7',
                'alpha',
                'regex:/^[A-Z]/',
                'unique:users,username',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[0-9]/',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[!@#$%^&*(),.?":{}|<>]/',
            ],
            'c_password' => [
                'required',
                'same:password',
            ],
            'birthday' => [
                'required',
                'date',
                'date_format:Y-m-d',
            ],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'message' => 'Error validation.',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
