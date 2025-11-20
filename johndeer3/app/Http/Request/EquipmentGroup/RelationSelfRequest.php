<?php

namespace App\Http\Request\EquipmentGroup;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(type="object", title="Request for attach eg",
 *     @OA\Property(property="egs", title="Egs", description="Ids equipment-group", example="[1,4]")
 * )
 */
class RelationSelfRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'egs' => ['array'],
            'egs.*' => ['nullable', 'integer', 'exists:jd_equipment_groups,id'],
        ];
    }
}
