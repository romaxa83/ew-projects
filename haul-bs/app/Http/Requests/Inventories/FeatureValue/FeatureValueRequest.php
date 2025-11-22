<?php

namespace App\Http\Requests\Inventories\FeatureValue;

use App\Dto\Inventories\FeatureValueDto;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Modules\Seo\Traits\SeoRequestRules;
use App\Foundations\Traits\Requests\OnlyValidateForm;
use App\Models\Inventories\Features\Feature;
use App\Models\Inventories\Features\Value;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(type="object", title="FeatureValueRequest",
 *     required={"name", "slug", "feature_id"},
 *     @OA\Property(property="name", type="string", example="Size"),
 *     @OA\Property(property="slug", type="string", example="size"),
 *     @OA\Property(property="feature_id", type="intager", example="2"),
 *     @OA\Property(property="position", type="integer", example="2"),
 * )
 */

class FeatureValueRequest extends BaseFormRequest
{
    use OnlyValidateForm;
    use SeoRequestRules;

    public function rules(): array
    {
        $id = $this->route('id');

        $rule = [
            'name' => ['required', 'string'],
            'slug' => ['required', 'string',
                $id
                    ? Rule::unique(Value::TABLE, 'slug')->ignore($id)
                    : Rule::unique(Value::TABLE, 'slug')
            ],
            'feature_id' => ['required', 'integer', Rule::exists(Feature::TABLE, 'id')],
            'position' => ['nullable', 'integer'],
            'active' => ['nullable', 'boolean'],
        ];

        if($id){
            unset($rule['feature_id']);
        }

        return $rule;
    }

    public function getDto(): FeatureValueDto
    {
        return FeatureValueDto::byArgs($this->validated());
    }
}
