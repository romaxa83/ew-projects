<?php

namespace App\Http\Request\Feature;

use App\Models\Report\Feature\Feature;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(type="object", title="Request for create feature",
 *     @OA\Property(property="type", type="int", example=1, enum={1, 2},
 *          description="Для чего харак. (1 - для поля, 2 - для машин)"
 *     ),
 *     @OA\Property(property="type_field", type="int", example=3, enum={0, 1, 2, 3},
 *          description="Тип поля при выборе данных в МП (0 - integer, 1 - string, 2 - boolean, 3 - select)"
 *     ),
 *     @OA\Property(property="position", type="int", example=3,
 *          description="Позиция для сортировки"
 *     ),
 *     @OA\Property(property="name", title="Name", description="Название характеристики", type="object",
 *          @OA\Property(property="ru", type="string", example="Состояние поля",
 *              enum={"bg", "cz", "da", "de", "el", "en", "es", "et", "fi", "fr", "hr", "hu", "it", "lt", "lv", "nl", "nn", "pl", "pt", "ro", "ru", "sk", "sr", "sv", "ua"},
 *              description="ключ - локаль, значение - название харак. для этой локали"
 *          )
 *     ),
 *     @OA\Property(property="unit", title="Unit", description="Название ед. измерения, для характ., может отсутствовать", type="object",
 *          @OA\Property(property="ru", type="string", example="км",
 *              enum={"bg", "cz", "da", "de", "el", "en", "es", "et", "fi", "fr", "hr", "hu", "it", "lt", "lv", "nl", "nn", "pl", "pt", "ro", "ru", "sk", "sr", "sv", "ua"},
 *              description="ключ - локаль, значение - название харак. для этой локали"
 *          )
 *     ),
 *     @OA\Property(property="egs", type="array", description="ID equipment group, к которым относится данная характеристика",
 *         @OA\Items(), example="[1,4]"
 *     ),
 *     @OA\Property(property="sub_egs", type="array", description="ID equipment group, к которым относится данная характеристика, и это поле будет дополнительным",
 *         @OA\Items(), example="[12,8]"
 *     ),
 *     required={"name", "type", "type_field", "position"},
 * )
 */

class FeatureCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:'.Feature::TYPE_GROUND.','.Feature::TYPE_MACHINE],
            'type_field' => ['required', 'in:'.Feature::TYPE_FIELD_INT_FOR_FRONT.','.Feature::TYPE_FIELD_STRING_FOR_FRONT.','.Feature::TYPE_FIELD_BOOL_FOR_FRONT.','.Feature::TYPE_FIELD_SELECT_FOR_FRONT],
            'name' => ['required'],
            'unit' => ['nullable'],
            'egs' => ['nullable'],
            'sub_egs' => ['nullable'],
            'position' => ['required', 'integer'],
        ];
    }
}
