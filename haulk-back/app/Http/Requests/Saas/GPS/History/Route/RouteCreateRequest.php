<?php

namespace App\Http\Requests\Saas\GPS\History\Route;

use Illuminate\Foundation\Http\FormRequest;

class RouteCreateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'truck_id' => ['nullable', 'integer'],
            'trailer_id' => ['nullable', 'integer'],
            'date' => ['required', 'date'],
            'data' => ['required', 'array'],
//            'data.*.location' => ['required', 'array'],
//            'data.*.location.lat' => ['required', 'numeric'],
//            'data.*.location.lng' => ['required', 'numeric'],
//            'data.*.speeding' => ['nullable', 'boolean'],
//            'data.*.timestamp' => ['nullable', 'integer'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
