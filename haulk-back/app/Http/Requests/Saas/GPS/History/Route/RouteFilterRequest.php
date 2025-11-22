<?php

namespace App\Http\Requests\Saas\GPS\History\Route;

use Illuminate\Foundation\Http\FormRequest;

class RouteFilterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'truck_id' => ['nullable', 'integer'],
            'trailer_id' => ['nullable', 'integer'],
            'device_id' => ['nullable', 'integer'],
            'date' => ['required', 'date'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
