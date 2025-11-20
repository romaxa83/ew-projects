<?php

namespace App\Resources\Custom;

use App\Models\Report\Feature\FeatureValue;
use App\Models\Report\Feature\FeatureValueTranslates;
use App\Models\Report\Feature\ReportFeatureValue;
use Illuminate\Database\Eloquent\Collection;

/**
 * @OA\Schema(type="object", title="CustomReportFeatureValueResource",
 *     @OA\Property(property="id", type="integer", description="ID value", example=1),
 *     @OA\Property(property="name", type="string", description="Название характеристики", example="Висота культури"),
 *     @OA\Property(property="unit", type="string", description="Ед. измерения", example="см"),
 *     @OA\Property(property="type", type="integer", example=1,
 *         description="Тип характеристики, для чего она предназначена (1 - для поля, 2 - для машин)"
 *     ),
 *     @OA\Property(property="type_field", type="integer", example=4,
 *         description="Тип поля при выборе данных (0 - integer, 1 - string, 2 - boolean, 3 - select)"
 *     ),
 *     @OA\Property(property="is_sub", type="boolean", description="Относится ли к прицепной техники", example=true),
 *     @OA\Property(property="group", type="array", @OA\Items(
 *             @OA\Property(property="id", type="integer", description="ID техники (ModelDescriptionID)", example=33),
 *             @OA\Property(property="name", type="string", description="Название техники (ModelDescription)", example="AMX-30"),
 *             @OA\Property(property="value", type="string", description="Значение", example="120.99"),
 *             @OA\Property(property="choiceId", type="string", description="ID значение (если оно выбираемое, может отсутствовать)", example=9),
 *         )
 *     ),
 * )
 */
class CustomReportFeatureValueResource
{
    private $list = [];

    public function fill(Collection $data, $onlyInt = true)
    {

        if($data->isNotEmpty()){
            $count = 0;
            foreach ($data as $key => $item){
                if($onlyInt){
                    if($item->feature->isIntegerField()){
                        /** @var $item ReportFeatureValue */
                        $this->processing($item, $key, $count);
                        $count++;
                    }
                } else {
                    /** @var $item ReportFeatureValue */
                    $this->processing($item, $key, $count);
                    $count++;
                }
            }
        }

        return $this->list;
    }

    private function processing(ReportFeatureValue $item, $key, $count)
    {
        if($this->hasFeature($item->feature->id) !== null){
            $k = $this->hasFeature($item->feature->id);
            $this->list[$k]['group'][$count]['id'] = $this->prettyVar($item->value->model_description_id);
            $this->list[$k]['group'][$count]['name'] = $this->prettyVar($item->value->model_description_name);
            $this->list[$k]['group'][$count]['value'] = $this->prettyVar(null != $item->value->value_id
                ? $item->value->valueCurrent->name ?? null
                : $item->value->value);
            $this->list[$k]['group'][$count]['choiceId'] = $this->prettyVar($item->value->value_id);
        } else {
            $this->list[$key]['id'] = $item->feature->id;
            $this->list[$key]['name'] = $item->feature->current->name ?? null;
            $this->list[$key]['unit'] = $item->feature->current->unit ?? null;
            $this->list[$key]['type'] = $item->feature->type;
            $this->list[$key]['type_field'] = $item->feature->type_field_for_front;
            $this->list[$key]['is_sub'] = $item->is_sub;
            // values
            $this->list[$key]['group'][$count]['id'] = $this->prettyVar($item->value->model_description_id);
            $this->list[$key]['group'][$count]['name'] = $this->prettyVar($item->value->model_description_name);
            $this->list[$key]['group'][$count]['value'] = $this->prettyVar(null != $item->value->value_id
                ? $item->value->valueCurrent->name ?? null
                : $item->value->value);
            $this->list[$key]['group'][$count]['choiceId'] = $this->prettyVar($item->value->value_id);
        }
    }

    private function prettyVar($value)
    {
        if($value == "null"){
            $value = null;
        }
        if($value == "false"){
            $value = false;
        }
        if($value == "true"){
            $value = true;
        }

        return $value;
    }

    private function hasFeature($id)
    {
        $has = null;
        foreach ($this->list as $key => $item){
            if(isset($item['id']) && $item['id'] == $id){
                $has = $key;
                break;
            }
        }

        return $has;
    }
}

