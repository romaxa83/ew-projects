<?php

namespace App\Http\Requests\Api\V1\Recommendation;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'uuid'  => ['nullable', 'string'],
            'auto'  => ['required', 'string', 'exists:user_cars,uuid'],
            'recommendation'  => ['required', 'string'],
            'comment'  => ['nullable', 'string'],
            'quantity'  => ['nullable'],
            'request'  => ['nullable', 'string', 'exists:orders,uuid'],
            'rejectionReason'  => ['nullable', 'string'],
            'dateCompletion'  => ['nullable', 'integer'],
            'author'  => ['nullable', 'string'],
            'executor'  => ['nullable', 'string'],
            'completed'  => ['nullable', 'boolean'],
            'dateRelevance'  => ['nullable', 'integer'],
        ];
    }
}

