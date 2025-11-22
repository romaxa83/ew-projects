<?php

namespace App\Http\Requests\Localizations;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(type="object", title="TranslationFilterRequest",
 *     @OA\Property(property="key", type="string", example="button.create"),
 *     @OA\Property(property="place", type="string", example="site"),
 *     @OA\Property(property="lang", type="string", example="en"),
 *     @OA\Property(property="text", type="string", example="create"),
 * )
 */
class TranslationFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => ['nullable', 'string'],
            'place' => ['nullable', 'string'],
            'lang' => ['nullable', 'string'],
            'text' => ['nullable', 'string'],
        ];
    }
}
