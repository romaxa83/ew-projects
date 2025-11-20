<?php

namespace App\Http\Request\Translation;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(type="object", title="SetTranslationRequest",
 *     example={
 *          "message::no_access" : {
 *              "en": "access denied",
 *              "ru": "доступ откланен",
 *          },
 *          "button" : {
 *              "en": "button",
 *              "ru": "кнопка",
 *          }
 *      }
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
        return [];
    }
}
