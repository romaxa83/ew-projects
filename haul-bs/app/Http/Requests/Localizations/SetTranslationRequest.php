<?php

namespace App\Http\Requests\Localizations;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(type="array", title="SetTranslationRequest", @OA\Items(
 *       @OA\Property(property="key", type="string", example="button.create"),
 *       @OA\Property(property="place", type="string", example="site"),
 *       @OA\Property(property="lang", type="string", example="en"),
 *       @OA\Property(property="text", type="string", example="create"),
 *   )
 * )
 */
class SetTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            '*.key' => ['required', 'string'],
            '*.place' => ['required', 'string'],
            '*.lang' => ['required', 'string'],
            '*.text' => ['required', 'string'],
        ];
    }
}


