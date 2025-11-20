<?php

namespace App\Http\Request;

use Illuminate\Foundation\Http\FormRequest;

class RequestSearch extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['required', 'string'],
        ];
    }
}
