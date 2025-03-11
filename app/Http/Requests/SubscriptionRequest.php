<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'email' => ['required', 'email', 'max:255'],
            'city' => ['required', 'string', 'min:2', 'max:255'],
            'country_code' => ['required', 'string', 'size:2'],
            'frequency' => ['sometimes', 'string', Rule::in(['daily', 'weekly', 'monthly'])],
            'notification_time' => ['sometimes', 'date_format:H:i:s'],
            'is_active' => ['sometimes', 'boolean'],
        ];

        if ($this->isMethod('POST')) {
            $rules['email'][] = Rule::unique('subscriptions')->where(function ($query) {
                return $query->where('city', $this->city)
                    ->where('country_code', $this->country_code);
            });
        }

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['email'] = [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('subscriptions')
                    ->where(function ($query) {
                        return $query->where('city', $this->city)
                            ->where('country_code', $this->country_code);
                    })
                    ->ignore($this->route('id'))
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email address is required',
            'email.email' => 'Please provide a valid email address',
            'email.unique' => 'This email is already subscribed for this city and country',
            'city.required' => 'City is required',
            'city.min' => 'City must be at least 2 characters',
            'country_code.required' => 'Country code is required',
            'country_code.size' => 'Country code must be exactly 2 characters',
            'frequency.in' => 'Frequency must be one of: daily, weekly, or monthly',
            'notification_time.date_format' => 'Notification time must be in HH:MM:SS format',
        ];
    }
} 