<?php

namespace App\Http\Request\Catalog\JD;

use Illuminate\Foundation\Http\FormRequest;

class MdListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'eg_id' => ['nullable', 'exists:jd_equipment_groups,id'],
        ];
    }
}
