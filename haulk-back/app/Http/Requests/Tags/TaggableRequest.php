<?php

namespace App\Http\Requests\Tags;

use App\Models\Tags\Tag;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaggableRequest extends FormRequest
{
    use OnlyValidateForm;

    protected int $maxTagsCount = 2;

    protected string $tagType = Tag::TYPE_ORDER;

    public function rules(): array
    {
        return [
            'tags' => ['nullable' ,'array', 'max:' . $this->maxTagsCount],
            'tags.*' => [
                'required',
                'int',
                Rule::exists(Tag::TABLE_NAME, 'id')
                    ->where('type', $this->tagType)
            ],
        ];
    }
}
