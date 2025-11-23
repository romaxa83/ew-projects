<?php

namespace WezomCms\ProductReviews\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Request for create review",
 *     required={"like", "text"}
 * )
 */
class ReviewRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'parent_id' => [
                'nullable',
                Rule::exists('product_reviews', 'id'),
            ],
            'like' => ['required', 'boolean'],
            'text' => ['required', 'string', 'max:65535'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
        ];
    }

    public function attributes(): array
    {
        return [];
    }

    public function messages(): array
    {
        return [
            'like.required' => __('cms-product-reviews::admin.validation.like.required'),
            'text.required' => __('cms-product-reviews::admin.validation.text.required'),
            'parent_id.exists' => __('cms-product-reviews::admin.validation.parent_id.exists'),
        ];
    }

    /**
     * @OA\Property(property="parent_id", title="Parent id", description="Id родительского отзыва", example=1)
     * @OA\Property(property="like", title="Like", description="Лайк - true, дизлайк - false", example=true)
     * @OA\Property(property="text", title="Text", description="Текст отзыва", example="some text review")
     * @OA\Property(property="rating", title="Rating", description="Рейтинг, допустимое занчение от 1 до 5", example=3)
     */
}
