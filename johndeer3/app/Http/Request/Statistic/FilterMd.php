<?php

namespace App\Http\Request\Statistic;

use Illuminate\Foundation\Http\FormRequest;

class FilterMd extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'year' => ['required'],
            'country' => ['required'],
            'dealer' => ['required'],
            'eg' => ['required'],
        ];
    }
}

