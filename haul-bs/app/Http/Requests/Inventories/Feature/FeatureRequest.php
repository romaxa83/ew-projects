<?php

namespace App\Http\Requests\Inventories\Feature;

use App\Dto\Inventories\FeatureDto;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Modules\Seo\Traits\SeoRequestRules;
use App\Foundations\Traits\Requests\OnlyValidateForm;
use App\Models\Inventories\Features\Feature;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(type="object", title="FeatureRequest",
 *     required={"name", "slug"},
 *     @OA\Property(property="name", type="string", example="Size"),
 *     @OA\Property(property="slug", type="string", example="size"),
 *     @OA\Property(property="multiple", type="boolean", example="true"),
 *     @OA\Property(property="active", type="boolean", example="true"),
 *     @OA\Property(property="position", type="integer", example="2"),
 * )
 */

class FeatureRequest extends BaseFormRequest
{
    use OnlyValidateForm;
    use SeoRequestRules;

    public function rules(): array
    {
        $id = $this->route('id');

        return array_merge(
            [
                'name' => ['required', 'string'],
                'slug' => ['required', 'string',
                    $id
                        ? Rule::unique(Feature::TABLE, 'slug')->ignore($id)
                        : Rule::unique(Feature::TABLE, 'slug')
                ],
                'multiple' => ['nullable', 'boolean'],
                'active' => ['nullable', 'boolean'],
                'position' => ['nullable', 'integer'],
            ]
        );
    }

    public function getDto(): FeatureDto
    {
        return FeatureDto::byArgs($this->validated());
    }
}
