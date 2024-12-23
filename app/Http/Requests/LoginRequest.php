<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'min:7',
                'alpha',
                'regex:/^[A-Z].*/'
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
