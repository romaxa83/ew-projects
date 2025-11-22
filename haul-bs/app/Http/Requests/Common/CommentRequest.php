<?php

namespace App\Http\Requests\Common;

use App\Foundations\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(type="object", title="CommentRequest",
 *     required={"comment"},
 *     @OA\Property(property="comment", type="string", example="some text"),
 * )
 *
 * @property string comment
 */

class CommentRequest extends FormRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return [
            'comment' => ['required', 'string', 'min:2', 'max:2000'],
        ];
    }
}
