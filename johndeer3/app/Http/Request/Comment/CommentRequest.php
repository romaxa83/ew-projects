<?php

namespace App\Http\Request\Comment;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(type="object", title="Request for create comment",
 *     required={"comment"},
 *     @OA\Property(property="comment", title="Comment", description="Комментарий к отчету", example="отличный отчет")
 * )
 */
class CommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'comment' => ['required', 'string'],
        ];
    }


}

