<?php

namespace App\Resources\JD;

use App\Helpers\DateFormat;
use App\Models\JD\EquipmentGroup;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @OA\Schema(type="object", title="EquipmentGroup Resource",
 *     @OA\Property(property="id", type="integer", description="ID", example=1),
 *     @OA\Property(property="jd_id", type="integer", description="ID из BOED", example=4),
 *     @OA\Property(property="name", type="string", description="Название", example="Combines"),
 *     @OA\Property(property="created", type="string", description="Создание", example="22.06.2020 10:04"),
 *     @OA\Property(property="updated", type="string", description="Обновление", example="22.06.2020 10:04"),
 *     @OA\Property(property="model_descriptions", description="Привязанные model descriptions", type="array", @OA\Items(
 *          ref="#/components/schemas/ModelDescriptionResource"
 *     )),
 *     @OA\Property(property="egs", description="Привязанные equipment group", type="array", @OA\Items(
 *          @OA\Property(property="id", type="integer", description="ID", example=5),
 *          @OA\Property(property="name", type="string", description="Название", example="Tillage"),
 *     ))
 * )
 */
class EquipmentGroupResource extends JsonResource
{
    public function toArray($request): array
    {
        $withMD = true;
        if($request['withoutMD'] && filter_var($request['withoutMD'], FILTER_VALIDATE_BOOLEAN)){
            $withMD = false;
        }

        /** @var EquipmentGroup $eg */
        $eg = $this;

        $data = [
            'id' => $eg->id,
            'jd_id' => $eg->jd_id,
            'name' => $eg->name,
            'created' => DateFormat::front($eg->created_at),
            'updated' => DateFormat::front($eg->updated_at),
            'egs' => $eg->relatedEgs->isNotEmpty() ? $this->relateEgs($eg->relatedEgs) : null
        ];

        if($withMD){
            $data['model_descriptions'] = ModelDescriptionResource::collection($eg->modelDescriptions);
        }
        return $data;
    }

    private function relateEgs(Collection $egs)
    {
        $data = [];
        foreach ($egs ?? [] as $key => $eg){
            $data[$key]['id'] = $eg['id'];
            $data[$key]['name'] = $eg['name'];
        }

        return $data;
    }
}
