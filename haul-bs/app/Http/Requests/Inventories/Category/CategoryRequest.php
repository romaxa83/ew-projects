<?php

namespace App\Http\Requests\Inventories\Category;

use App\Dto\Inventories\CategoryDto;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Modules\Seo\Traits\SeoRequestRules;
use App\Foundations\Traits\Requests\OnlyValidateForm;
use App\Models\Inventories\Brand;
use App\Models\Inventories\Category;
use App\Rules\Inventories\CategoryParentNullableRule;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(type="object", title="CategoryRequest",
 *     required={"name", "slug", "parent_id", "position"},
 *     @OA\Property(property="name", type="string", example="Ram Cooling system parts"),
 *     @OA\Property(property="slug", type="string", example="ram-cooling-system-parts"),
 *     @OA\Property(property="desc", type="string", example="some desc"),
 *     @OA\Property(property="parent_id", type="integer", example="1"),
 *     @OA\Property(property="position", type="integer", example="1"),
 *     @OA\Property(property="display_menu", type="boolean", example="true"),
 *     @OA\Property(property="seo", type="object", ref="#/components/schemas/SeoRequest"),
 *     @OA\Property(property="header_image",type="string", format="binary", nullable=true , description="The file to upload"),
 *     @OA\Property(property="menu_image", type="string", format="binary", nullable=true , description="The file to upload"),
 *     @OA\Property(property="mobile_image", type="string", format="binary", nullable=true , description="The file to upload"),
 * )
 */

class CategoryRequest extends BaseFormRequest
{
    use OnlyValidateForm;
    use SeoRequestRules;

    public function rules(): array
    {
        $id = $this->route('id');

        return array_merge(
            $this->seoRules(),
            [
                'name' => ['required', 'string'],
                'slug' => ['required', 'string',
                    $id
                        ? Rule::unique(Category::TABLE, 'slug')->ignore($id)
                        : Rule::unique(Category::TABLE, 'slug')
                ],
                'desc' => ['nullable', 'string', 'max:500'],
                'parent_id' => [
                    $id
                        ? new CategoryParentNullableRule($id)
                        : 'required',
                    Rule::when($this->get('parent_id') != null, [
                        Rule::exists(Category::TABLE, 'id')
                    ]),
                ],
                'position' => ['required', 'integer'],
                'display_menu' => ['nullable'],
                Category::IMAGE_HEADER_FIELD_NAME => ['nullable', 'image',
                    "max:" . byte_to_kb(config('media-library.max_file_size'))
                ],
                Category::IMAGE_MENU_FIELD_NAME => ['nullable', 'image',
                    "max:" . byte_to_kb(config('media-library.max_file_size'))
                ],
                Category::IMAGE_MOBILE_FIELD_NAME => ['nullable', 'image',
                    "max:" . byte_to_kb(config('media-library.max_file_size'))
                ],
            ]
        );
    }

    public function getDto(): CategoryDto
    {
        return CategoryDto::byArgs($this->validated());
    }
}
