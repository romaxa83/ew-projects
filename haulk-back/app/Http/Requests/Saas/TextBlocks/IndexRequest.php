<?php

namespace App\Http\Requests\Saas\TextBlocks;

use App\Dto\Saas\TextBlocks\IndexDto;
use App\Http\Requests\Saas\BaseSassRequest;
use App\Models\Saas\TextBlock;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Validation\Rule;

class IndexRequest extends BaseSassRequest
{

    use OnlyValidateForm;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                'query' => [
                    'nullable',
                    'string'
                ],
                'group' => [
                    'nullable',
                    'string',
                    Rule::in(array_keys(TextBlock::TB_GROUPS))
                ],
                'scope' => [
                    'nullable',
                    'array'
                ],
                'scope.*' => [
                    'required',
                    'string',
                    Rule::in(array_keys(TextBlock::TB_SCOPES))
                ]
            ]
        );
    }

    public function dto(): IndexDto
    {
        return IndexDto::fromRequest($this->validated());
    }
}
