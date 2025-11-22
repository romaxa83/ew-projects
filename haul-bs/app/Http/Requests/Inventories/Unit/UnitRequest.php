<?php

namespace App\Http\Requests\Inventories\Unit;

use App\Dto\Inventories\UnitDto;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Traits\Requests\OnlyValidateForm;

/**
 * @OA\Schema(type="object", title="UnitRequest",
 *     required={"name", "accept_decimals"},
 *     @OA\Property(property="name", type="string", example="inch"),
 *     @OA\Property(property="accept_decimals", type="boolean", example="false"),
 * )
 */

class UnitRequest extends BaseFormRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return  [
            'name' => ['required', 'string'],
            'accept_decimals' => ['required', 'boolean'],
        ];
    }

    public function getDto(): UnitDto
    {
        return UnitDto::byArgs($this->validated());
    }
}
