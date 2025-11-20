<?php

namespace App\Http\Request\Statistic\Report;

use Illuminate\Foundation\Http\FormRequest;

class FilterCountry extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'year' => ['required'],
            'status' => ['required'],
        ];
    }
}
