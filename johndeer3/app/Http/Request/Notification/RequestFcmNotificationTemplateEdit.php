<?php

namespace App\Http\Request\Notification;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(type="object", title="Request FcmNotification Template Edit",
 *     @OA\Property(property="translations", title="Translations", type="object",
 *          @OA\Property(property="lang", type="object",
 *                  @OA\Property(property="title", type="string", example="some title"),
 *                  @OA\Property(property="text", type="string", example="some text"),
 *           ),
 *     )
 * )
 */

class RequestFcmNotificationTemplateEdit extends FormRequest
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
