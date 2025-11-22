<?php

namespace App\Http\Requests\Api\OneC\Catalog\Categories;

use App\Http\Requests\BaseFormRequest;
use App\Models\Catalog\Categories\Category;
use App\Models\Localization\Language;
use App\Rules\TranslationsArrayValidator;
use Illuminate\Validation\Rule;

class CategoryCreateRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'guid' => ['required', 'uuid', Rule::unique(Category::class, 'guid')],
            'active' => ['required', 'bool'],
            'parent_guid' => ['nullable', 'uuid', Rule::exists(Category::class, 'guid')],
            'slug' => ['required', 'string', Rule::unique(Category::class, 'slug')],
            'translations' => [new TranslationsArrayValidator()],
            'translations.*.title' => ['required', 'string'],
            'translations.*.description' => ['nullable', 'string'],
            'translations.*.language' => ['required', 'string', 'distinct', Rule::exists(Language::TABLE, 'slug')],
        ];
    }
}
