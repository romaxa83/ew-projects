<?php

namespace App\Http\Request\Statistic\Report;

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
            'status' => ['required'],
            'country' => ['required'],
        ];
    }
}
