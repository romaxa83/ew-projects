<?php

namespace App\Http\Request\Feature;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(type="object", title="Request for create feature's value",
 *     @OA\Property(property="ru", type="string", example="озимая пшеница",
 *         enum={"bg", "cz", "da", "de", "el", "en", "es", "et", "fi", "fr", "hr", "hu", "it", "lt", "lv", "nl", "nn", "pl", "pt", "ro", "ru", "sk", "sr", "sv", "ua"},
 *         description="ключ - локаль, значение - название харак. для этой локали"
 *     ),
 *     @OA\Property(property="ua", type="string", example="озима пшениця",
 *         enum={"bg", "cz", "da", "de", "el", "en", "es", "et", "fi", "fr", "hr", "hu", "it", "lt", "lv", "nl", "nn", "pl", "pt", "ro", "ru", "sk", "sr", "sv", "ua"},
 *         description="ключ - локаль, значение - название харак. для этой локали"
 *     ),
 *     @OA\Property(property="en", type="string", example="winter wheat",
 *         enum={"bg", "cz", "da", "de", "el", "en", "es", "et", "fi", "fr", "hr", "hu", "it", "lt", "lv", "nl", "nn", "pl", "pt", "ro", "ru", "sk", "sr", "sv", "ua"},
 *         description="ключ - локаль, значение - название харак. для этой локали"
 *     )
 * )
 */

class ValueCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
