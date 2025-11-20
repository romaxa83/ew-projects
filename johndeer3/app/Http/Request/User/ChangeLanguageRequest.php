<?php

namespace App\Http\Request\User;

use App\Models\Translate;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(type="object", title="Change Language Request",
 *     @OA\Property(property="lang", type="string", description="Локаль", example="en",
 *          enum={"bg", "cz", "da", "de", "el", "en", "es", "et", "fi", "fr", "hr", "hu", "it", "lt", "lv", "nl", "nn", "pl", "pt", "ro", "ru", "sk", "sr", "sv", "ua"},
 *     ),
 *     required={"lang"}
 * )
 */

class ChangeLanguageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lang' => ['required', 'string'],
        ];
    }
}
