<?php

namespace App\Http\Request\Page;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(type="object", title="PageUpdateRequest",
 *     @OA\Property(property="name", title="Заголовок", type="object",
 *          @OA\Property(property="ru", type="string", example="title_ru"),
 *          @OA\Property(property="ua", type="string", example="title_ua"),
 *          @OA\Property(property="en", type="string", example="title_en"),
 *     ),
 *     @OA\Property(property="text", title="Текст", type="object",
 *          @OA\Property(property="ru", type="string", example="text_ru"),
 *          @OA\Property(property="ua", type="string", example="text_ua"),
 *          @OA\Property(property="en", type="string", example="text_en"),
 *     ),
 *     required={"name", "text"}
 * )
 */

class PageUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'name' => ['required'],
            'text' => ['required'],
        ];
    }
}
