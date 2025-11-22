<?php

namespace App\Http\Requests\Api\OneC\Catalog\Features;

use App\Dto\Catalog\FeatureDto;
use App\Http\Requests\BaseFormRequest;
use App\Models\Catalog\Features\Feature;
use App\Traits\Http\Requests\SimpleTranslationRulesTrait;
use Illuminate\Validation\Rule;

class FeatureRequest extends BaseFormRequest
{
    use SimpleTranslationRulesTrait;

    public function rules(): array
    {
        return array_merge(
            [
                'active' => ['nullable', 'boolean'],
            ],
            $this->getGuidRule(),
            $this->getTranslationRules()
        );
    }

    protected function getGuidRule(): array
    {
        if ($this->feature) {
            return [];
        }

        return [
            'guid' => ['required', 'uuid', Rule::unique(Feature::class, 'guid')],
        ];
    }

    public function getDto(): FeatureDto
    {
        return FeatureDto::byArgs($this->validated());
    }
}
