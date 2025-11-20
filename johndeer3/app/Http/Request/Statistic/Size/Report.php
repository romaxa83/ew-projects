<?php

namespace App\Http\Request\Statistic\Size;

use Illuminate\Foundation\Http\FormRequest;

class Report extends FormRequest
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
            'dealer' => ['required'],
            'eg' => ['required'],
            'md' => ['required'],
            'size' => ['required'],
        ];
    }
}
