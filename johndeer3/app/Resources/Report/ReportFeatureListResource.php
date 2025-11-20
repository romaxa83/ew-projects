<?php

namespace App\Resources\Report;

use App\Models\Report\Feature\Feature;
use App\Resources\Custom\CustomFeatureValueResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(type="object", title="Report Feature List Resource", description="В терминологии МП - это поля таблицы",
 *     @OA\Property(property="id", type="integer", description="ID характеристики", example=1),
 *     @OA\Property(property="name", type="string", description="Название характеристики", example="Якість подрібнення / розподілення"),
 *     @OA\Property(property="unit", type="string", description="Ед. измерения характеристика (может быть null)", example="%"),
 *     @OA\Property(property="type", type="integer", description="К чему, условно, относится характеристика (1 - условие на поле, 2 - к технике)", example=2),
 *     @OA\Property(property="position", type="integer", description="Позиция для сортировки", example=2),
 *     @OA\Property(property="type_field", type="integer", description="Тип значение для характеристики, для вывода соответствующего поля (0 - integer,1 - string,2 - boolean,3 - select)", example=3),
 *     @OA\Property(property="active", type="boolean", description="Активна характеристика", example=true),
 *     @OA\Property(property="values", type="array", description="Значения для выбора,если такие заданы для данной характеристики",
 *         @OA\Items(
 *             @OA\Property(property="id", type="integer", description="ID значения характеристик", example=1),
 *             @OA\Property(property="name", type="integer", description="Название значений", example="задовільна"),
 *         )
 *     ),
 *     @OA\Property(property="egs", type="array", example="[3,67,5]", @OA\Items(),
 *         description="Массив id - equipmentGroup, к которым применима данная характеристика"
 *     ),
 * )
 */

class ReportFeatureListResource extends JsonResource
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
            'name' => $feature->current->name ?? null,
            'unit' => $feature->current->unit ?? null,
            'position' => $feature->position,
            'type' => $feature->type,
            'type_field' => $feature->type_field_for_front,
            'egs' => $feature->egs ? $this->egIds($feature->egs) : null,
            'values' => \App::make(CustomFeatureValueResource::class)->fill($feature->values, false, true),
            'group' => [],
            'active' => $feature->active,
        ];
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
