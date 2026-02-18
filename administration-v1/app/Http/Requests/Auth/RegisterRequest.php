<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $commonRules = [
            'type' => 'required|in:rider,driver,admin',
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|string|email|max:100|unique:users',
            'phone' => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
        ];

        $driverRules = [
            'driver_license_number' => 'required_if:type,driver|string|max:50|unique:users',
            'driver_license_expiry' => 'required_if:type,driver|date',
            'vehicle_type' => 'required_if:type,driver|string|max:50',
            'vehicle_model' => 'required_if:type,driver|string|max:100',
            'vehicle_year' => 'required_if:type,driver|integer|min:1900|max:' . (date('Y') + 1),
            'vehicle_plate_number' => 'required_if:type,driver|string|max:20|unique:users',
            'vehicle_color' => 'required_if:type,driver|string|max:30',
        ];

        $riderRules = [
            'preferred_payment_method' => 'nullable|string|in:cash,card,mobile_money',
        ];

        return array_merge(
            $commonRules,
            $this->input('type') === 'driver' ? $driverRules : [],
            $this->input('type') === 'rider' ? $riderRules : []
        );
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Please select user type (rider or driver)',
            'driver_license_number.required_if' => 'Driver license number is required for drivers',
            'vehicle_plate_number.required_if' => 'Vehicle plate number is required for drivers',
        ];
    }
}