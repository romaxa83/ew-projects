<?php

namespace App\Http\Requests\Saas\TextBlocks;

use App\Dto\Saas\TextBlocks\TextBlockDto;
use App\Models\Saas\TextBlock;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{

    use OnlyValidateForm;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $block = $this->block;
        $group = $this->group;
        $scope = $this->scope ?: [];

        return [
            'group' => [
                'required',
                'string',
                $this->getGroupRule()
            ],
            'block' => [
                'required',
                'string',
                'max:255',
                Rule::unique(TextBlock::TABLE_NAME)->where(
                    function(Builder $query)  use($block, $group, $scope) {
                        return $query->where('block', $block)
                            ->where('group', $group)
                            ->where('scope', json_encode($scope));
                    }
                )
            ],
            'scope' => [
                'required',
                'array'
            ],
            'scope.*' => [
                'required',
                Rule::in(array_keys(TextBlock::TB_SCOPES))
            ],
            'en' => [
                'required',
                'string'
            ],
            'es' => [
                'nullable',
                'string'
            ],
            'ru' => [
                'nullable',
                'string'
            ]
        ];
    }

    public function dto(): TextBlockDto
    {
        return TextBlockDto::fromRequest($this->validated());
    }

    private function getGroupRule(): string
    {
        $groups = array_keys(TextBlock::TB_GROUPS);
        return 'in:' . implode(',', $groups);
    }
}
