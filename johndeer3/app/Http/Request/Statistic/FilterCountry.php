<?php

namespace App\Http\Request\Statistic;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(type="object", title="Filter Country Request",
 *     @OA\Property(property="year", type="string", description="Год"
 *     ),
 *     required={"year"},
 * )
 */

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
        ];
    }
}
