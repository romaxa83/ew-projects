<?php

namespace App\Http\Requests\Vehicles\Trailers;

use App\Models\Vehicles\Trailer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GPSDevicesListRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'trailer_id' => ['nullable', 'integer', Rule::exists(Trailer::TABLE_NAME, 'id')],
        ];
    }
}
