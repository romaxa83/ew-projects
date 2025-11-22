<?php

namespace App\Http\Requests\Api\V1\Recommendation;

use Illuminate\Foundation\Http\FormRequest;

class EditRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'recommendation'  => ['required', 'string'],
            'comment'  => ['nullable', 'string'],
            'quantity'  => ['nullable'],
            'rejectionReason'  => ['nullable', 'string'],
            'dateCompletion'  => ['nullable', 'string'],
            'author'  => ['nullable', 'string'],
            'executor'  => ['nullable', 'string'],
            'completed'  => ['required', 'boolean'],
            'dateRelevance'  => ['nullable', 'string'],
        ];
    }
}


