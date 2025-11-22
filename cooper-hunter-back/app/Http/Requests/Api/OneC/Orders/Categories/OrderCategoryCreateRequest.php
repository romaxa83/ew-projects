<?php

namespace App\Http\Requests\Api\OneC\Orders\Categories;

use App\Http\Requests\BaseFormRequest;
use App\Models\Localization\Language;
use App\Models\Orders\Categories\OrderCategory;
use App\Permissions\Orders\Categories\OrderCategoryCreatePermission;
use App\Rules\TranslationsArrayValidator;
use Illuminate\Validation\Rule;

class OrderCategoryCreateRequest extends BaseFormRequest
{
    public const PERMISSION = OrderCategoryCreatePermission::KEY;

    public function rules(): array
    {
        return [
            'guid' => ['required', 'uuid', Rule::unique(OrderCategory::class, 'guid')],
            'active' => ['nullable', 'boolean'],
            'translations' => ['required', 'array', new TranslationsArrayValidator()],
            'translations.*.language' => ['required', 'max:3', Rule::exists(Language::class, 'slug')],
            'translations.*.title' => ['required', 'string'],
            'translations.*.description' => ['nullable', 'string'],
        ];
    }
}
