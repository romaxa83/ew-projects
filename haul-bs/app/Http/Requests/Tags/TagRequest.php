<?php

namespace App\Http\Requests\Tags;

use App\Dto\Tags\TagDto;
use App\Enums\Tags\TagType;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Traits\Requests\OnlyValidateForm;

/**
 * @OA\Schema(type="object", title="TagRequest",
 *     required={"name", "color", "type"},
 *     @OA\Property(property="name", type="string", example="Empty"),
 *     @OA\Property(property="color", type="string", example="#2F54EB"),
 *     @OA\Property(property="type", type="string", example="trucks_and_trailer"),
 * )
 */

class TagRequest extends BaseFormRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:10'],
            'type' => ['required', 'string', TagType::ruleIn()],
        ];
    }

    public function getDto(): TagDto
    {
        return TagDto::byArgs($this->validated());
    }
}
