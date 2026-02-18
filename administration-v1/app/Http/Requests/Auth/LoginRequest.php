<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required_without:phone|email|exists:users,email',
            'phone' => 'required_without:email|string|exists:users,phone',
            'password' => 'required|string',
            'device_name' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'email.exists' => 'The provided email does not exist in our records.',
            'phone.exists' => 'The provided phone number does not exist in our records.',
        ];
    }
}