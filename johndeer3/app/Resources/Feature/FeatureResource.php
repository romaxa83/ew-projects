<?php

namespace App\Resources\Feature;

use App\Models\Report\Feature\Feature;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(type="object", title="FeatureResource",
 *     @OA\Property(property="id", type="integer", description="ID", example=1),
 *     @OA\Property(property="active", type="boolean", description="Активен ли шаблон", example=true),
 *     @OA\Property(property="type", type="integer", enum={1, 2}, example=1,
 *          description="Тип характеристики, для чего она предназначена (1 - для поля, 2 - для машин)"
 *     ),
 *     @OA\Property(property="type_field", type="integer", enum={0, 1, 2, 3}, example=2,
 *          description="Тип поля при выборе данных (0 - integer, 1 - string, 2 - boolean, 3 - select)"
 *     ),
 *     @OA\Property(property="name", title="Name", description="Название характеристики", type="object",
 *          @OA\Property(property="ru", type="string", example="Состояние поля",
 *               description="ключ - локаль, значение - название харак. для этой локали"
 *          ),
 *          @OA\Property(property="en", type="string", example="Field state",
 *               description="ключ - локаль, значение - название харак. для этой локали"
 *          ),
 *     ),
 *     @OA\Property(property="unit", title="Unit", description="Название ед. измерения, для характ., может отсутствовать", type="object",
 *          @OA\Property(property="ru", type="string", example="Состояние поля",
 *               enum={"bg", "cz", "da", "de", "el", "en", "es", "et", "fi", "fr", "hr", "hu", "it", "lt", "lv", "nl", "nn", "pl", "pt", "ro", "ru", "sk", "sr", "sv", "ua"},
 *               description="км"
 *          ),
 *          @OA\Property(property="en", type="string", example="Field state",
 *               description="km"
 *          ),
 *     ),
 *     @OA\Property(property="egs", type="array", description="ID equipment group, к которым относится данная характеристика",
 *         @OA\Items(), example="[1,4]"
 *     ),
 * )
 */
class FeatureResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Feature $feature */
        $feature = $this;
        if(!$feature){
            return [];
        }

        return [
            'id' => $feature->id,
            'type' => $feature->type,
            'position' => $feature->position,
            'type_field' => $feature->type_field_for_front,
            'name' => $this->byTranslationsField($feature->translations, 'name'),
            'unit' => $this->byTranslationsField($feature->translations, 'unit'),
            'active' => $feature->active,
            'egs' => $feature->egs ? $this->egIds($feature->egs) : null
        ];
    }

    private function byTranslationsField($translations, $field)
    {
        if($translations){
            $data = [];
            foreach($translations ?? [] as $translation){
                if($translation->{$field}){
                    $data[$translation->lang] = $translation->{$field};
                }
            }

            return !empty($data) ? $data : null;
        }

        return null;
    }

    private function egIds($egs)
    {
        $arr = [];
        foreach ($egs as $eg){
            array_push($arr, $eg->id);
        }
        return  $arr;
    }
}
