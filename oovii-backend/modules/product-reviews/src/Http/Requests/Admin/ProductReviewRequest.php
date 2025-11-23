<?php

namespace WezomCms\ProductReviews\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductReviewRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'published' => 'required',
            'product_id' => 'required|exists:products,id',
            'parent_id' => [
                'nullable',
                'required_if:admin_answer,1',
                Rule::exists('product_reviews', 'id')->whereNot('id', $this->route('product_review')),
            ],
            'already_bought' => 'required|bool',
            'admin_answer' => 'required|bool',
            'created_at' => 'required|date|before_or_equal:today',
//            'rating' => 'nullable|required_unless:admin_answer,1|int|min:1|max:5',
            'like' => 'required|bool',
            'name' => 'nullable|required_unless:admin_answer,1|string|max:255',
            'email' => 'nullable|required_unless:admin_answer,1|email|max:255',
            'text' => 'required|string|max:65535',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'published' => __('cms-core::admin.layout.Published'),
            'product_id' => __('cms-product-reviews::admin.Product'),
            'parent_id' => __('cms-product-reviews::admin.Parent review'),
            'already_bought' => __('cms-product-reviews::admin.Already bought'),
            'admin_answer' => __('cms-product-reviews::admin.Admin answer'),
            'rating' => __('cms-product-reviews::admin.Rating'),
            'like' => __('cms-product-reviews::admin.Like'),
            'name' => __('cms-product-reviews::admin.Name'),
            'email' => __('cms-product-reviews::admin.E-mail'),
            'text' => __('cms-product-reviews::admin.Text'),
            'created_at' => __('cms-product-reviews::admin.Created at'),
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'required_unless' => __('cms-product-reviews::admin.The attribute field is required unless'),
            'parent_id.required_if' => __(
                'cms-product-reviews::admin.The attribute field is required when admin answer'
            ),
        ];
    }
}
