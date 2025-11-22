<?php

namespace App\Http\Requests\Inventories\Brand;

use App\Dto\Inventories\BrandDto;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Modules\Seo\Traits\SeoRequestRules;
use App\Foundations\Traits\Requests\OnlyValidateForm;
use App\Models\Inventories\Brand;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(type="object", title="BrandRequest",
 *     required={"name", "slug"},
 *     @OA\Property(property="name", type="string", example="Bosch"),
 *     @OA\Property(property="slug", type="string", example="bosch"),
 *     @OA\Property(property="seo", type="object", ref="#/components/schemas/SeoRequest"),
 * )
 */

class BrandRequest extends BaseFormRequest
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
                        ? Rule::unique(Brand::TABLE, 'slug')->ignore($id)
                        : Rule::unique(Brand::TABLE, 'slug')
                ],
            ]
        );
    }

    public function getDto(): BrandDto
    {
        return BrandDto::byArgs($this->validated());
    }
}
