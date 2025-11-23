<?php

namespace WezomCms\Catalog\Http\Requests\Admin;

use Auth;
use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Core\Models\Administrator;
use WezomCms\Core\Traits\LocalizedRequestTrait;

class ProductRequest extends FormRequest
{
    use LocalizedRequestTrait;

    public function rules(): array
    {
        $rules = [
            'published' => 'required',
            'cost' => ['required', 'min:0', 'max:99999999'],
            'cost_discount' => ['nullable', 'min:0', 'max:99999999', 'lt:cost'],
            'amount' => ['required', 'int', 'min:0', 'max:99999999'],
            'amount_one_user' => ['required', 'int', 'min:0', 'max:99999999'],
            'published_at' => ['nullable', 'date_format:d.m.Y'],
            'expires_at' => ['nullable', 'date_format:d.m.Y', 'after:published_at'],
            'group_key' => ['nullable', 'string'],
            'weight' => ['required', 'integer', 'min:0'],
            'brand_id' => ['nullable'],
            'category_id' => ['nullable'],
            'bonus' => ['nullable', 'min:0', 'max:99999999', 'lt:cost'],
            'popular' => ['required', 'bool'],
            'best_price' => ['required', 'bool'],
            'moderated' => ['nullable', 'bool'],
            'dimensions' => ['required', 'array', 'size:3'],
            'dimensions.*' => ['required', 'integer', 'min:1', 'max:200'],
        ];

        /** @var Administrator $admin */
        $admin = Auth::user();

        if ($admin && !$admin->onlyProvider()) {
            $rules['provider_id'] = ['required', 'exists:administrators,id'];
            $rules['moderator_id'] = ['required', 'exists:administrators,id'];
        }

        return $this->localizeRules(
            [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'feature_1' => 'nullable|string',
                'feature_2' => 'nullable|string',
                'feature_3' => 'nullable|string',
            ],
            $rules
        );
    }

    public function attributes(): array
    {
        return $this->localizeAttributes(
            [
                'name' => __('cms-catalog::admin.products.Name'),
                'description' => __('cms-core::admin.seo.Description'),
                'feature_1' => __('cms-core::admin.products.feature_1'),
                'feature_2' => __('cms-core::admin.products.feature_2'),
                'feature_3' => __('cms-core::admin.products.feature_3'),
            ],
            [
                'published' => __('cms-core::admin.layout.Published'),
                'cost' => __('cms-catalog::admin.products.Cost'),
                'cost_discount' => __('cms-catalog::admin.products.cost discount'),
                'amount' => __('cms-catalog::admin.products.amount'),
                'amount_one_user' => __('cms-catalog::admin.products.amount one user'),
                'expires_at' => __('cms-catalog::admin.products.Expires at'),
                'published_at' => __('cms-catalog::admin.products.Published at'),
                'provider_id' => __('cms-providers::admin.provider.Provider'),
                'moderator_id' => __('cms-core::admin.moderator.moderator'),
                'bonus' => __('cms-catalog::admin.products.Bonus'),
                'popular' => __('cms-catalog::admin.products.Popular'),
                'sale' => __('cms-catalog::admin.products.Sale'),
                'best_price' => __('cms-catalog::admin.products.Best price'),
                'weight' => __('cms-catalog::admin.products.Weight'),
                'dimensions' => __('cms-catalog::admin.Dimensions'),
                'dimensions.0' => __('cms-catalog::admin.products.dimensions.Width'),
                'dimensions.1' => __('cms-catalog::admin.products.dimensions.Length'),
                'dimensions.2' => __('cms-catalog::admin.products.dimensions.Height'),
            ]
        );
    }

    public function messages(): array
    {
        return [
            'expires_at.after' => __('cms-catalog::admin.products.End date of the promotion'),
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->sometimes('expires_at', 'after:yesterday', function ($request) {
            return 1 == $request->get('sale');
        });
    }
}
