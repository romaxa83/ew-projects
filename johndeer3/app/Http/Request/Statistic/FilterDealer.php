<?php

namespace App\Http\Request\Statistic;

use Illuminate\Foundation\Http\FormRequest;

class FilterDealer extends FormRequest
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
        ];
    }
}
