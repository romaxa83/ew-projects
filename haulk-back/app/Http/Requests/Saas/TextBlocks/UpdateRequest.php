<?php

namespace App\Http\Requests\Saas\TextBlocks;

use App\Models\Saas\TextBlock;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;

class UpdateRequest extends StoreRequest
{

    use OnlyValidateForm;

    /**@var TextBlock $textBlock*/
    private $textBlock;

    public function prepareForValidation()
    {
        $this->textBlock = $this->route()->parameter('textBlock');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['block'] = [
            'required',
            'string',
            'max:255',
            Rule::unique(TextBlock::TABLE_NAME)->where(
                function(Builder $query) {
                    return $query->where('block', $this->block)
                        ->where('group', $this->group)
                        ->where('scope', json_encode($this->scope ?: []))
                        ->where('id', '<>', $this->textBlock->id);
                }
            )
        ];

        return $rules;
    }
}
