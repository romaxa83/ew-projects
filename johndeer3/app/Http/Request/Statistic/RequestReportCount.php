<?php

namespace App\Http\Request\Statistic;

use Illuminate\Foundation\Http\FormRequest;

class RequestReportCount extends FormRequest
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
        ];
    }
}
