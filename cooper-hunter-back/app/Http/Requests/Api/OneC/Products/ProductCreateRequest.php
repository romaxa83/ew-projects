<?php

namespace App\Http\Requests\Api\OneC\Products;

use App\Http\Requests\BaseFormRequest;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Features\Feature;
use App\Models\Catalog\Manuals\Manual;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Troubleshoots;
use App\Models\Catalog\Videos\VideoLink;
use App\Models\Localization\Language;
use App\Permissions\Catalog\Products\CreatePermission;
use App\Rules\TranslationsArrayValidator;
use Illuminate\Validation\Rule;

class ProductCreateRequest extends BaseFormRequest
{
    public const PERMISSION = CreatePermission::KEY;

    public function rules(): array
    {
        return [
            'guid' => ['required', 'uuid', Rule::unique(Product::class, 'guid')],
            'slug' => ['required', 'string', Rule::unique(Product::class, 'slug')],
            'active' => ['nullable', 'boolean'],
            'category_guid' => ['required', 'uuid', Rule::exists(Category::class, 'guid')],
            'title' => ['required', 'string'],
            'seer' => ['nullable', 'numeric', 'min:0'],
            'video_link_ids' => ['nullable', 'array'],
            'video_link_ids.*' => ['required', Rule::exists(VideoLink::class, 'id')],
            'features' => ['nullable', 'array'],
            'features.*.guid' => ['required', Rule::exists(Feature::class, 'guid')],
            'features.*.values' => ['required', 'array'],
            'features.*.values.*' => ['required', 'string'],
            'translations' => [new TranslationsArrayValidator()],
            'translations.*.language' => ['required', 'max:3', Rule::exists(Language::class, 'slug')],
            'translations.*.description' => ['nullable'],
            'relations' => ['nullable', 'array'],
            'relations.*' => ['required', 'uuid', Rule::exists(Product::class, 'guid')],
            'certificates' => ['nullable', 'array'],
            'certificates.*.type_name' => ['required', 'string'],
            'certificates.*.number' => ['required', 'string'],
            'certificates.*.link' => ['nullable', 'string'],
            'manual_ids' => ['nullable', 'array'],
            'manual_ids.*' => ['required', 'integer', Rule::exists(Manual::class, 'id')],
            'troubleshoot_group_ids' => ['nullable', 'array'],
            'troubleshoot_group_ids.*' => [
                'nullable',
                'array',
                Rule::exists(Troubleshoots\Group::class, 'id')
            ],
        ];
    }
}
