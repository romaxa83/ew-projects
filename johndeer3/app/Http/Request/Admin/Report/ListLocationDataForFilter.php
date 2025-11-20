<?php

namespace App\Http\Request\Admin\Report;

use App\Models\Report\Location;
use Illuminate\Foundation\Http\FormRequest;

class ListLocationDataForFilter extends FormRequest
{
    protected $userUpdate;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:'.Location::TYPE_COUNTRY_FILTER.','.Location::TYPE_REGION_FILTER.','.Location::TYPE_DISTRICT_FILTER],
            'query' => ['nullable', 'string'],
            'country' => ['nullable', 'string'],
            'forStatistic' => ['nullable'],
        ];
    }
}
