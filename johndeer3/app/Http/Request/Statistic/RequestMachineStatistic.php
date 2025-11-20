<?php

namespace App\Http\Request\Statistic;

use App\Models\Translate;
use Illuminate\Foundation\Http\FormRequest;

class RequestMachineStatistic extends FormRequest
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
            'dealerId' => ['required'],
            'eg' => ['required'],
            'md' => ['required'],
        ];
    }
}
