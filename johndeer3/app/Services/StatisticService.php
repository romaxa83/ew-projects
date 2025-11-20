<?php

namespace App\Services;

use App\Models\Report\Report;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

//todo переезда на v2 удалить
class StatisticService
{
    public function statisticMachine(EloquentCollection $data): array
    {
        $temp = [];
        foreach ($data as $key => $item){
            $temp[$item->user->dealer->name][] = $item;
        }
        foreach ($temp ?? [] as $dealerName => $reports){
            $temp[$dealerName]= collect($reports);
        }

        $finalTemp = [];
        $count = 0;
        foreach ($temp ?? [] as $dealerName => $collection){
            $finalTemp[$count]['dealer'] = $dealerName;
            $finalTemp[$count]['values'] = $this->dataProcessing($collection);
            $count++;
        }

        return $finalTemp;
    }

    private function dataProcessing(Collection $data)
    {
        $arr = [];
        $count = 0;
        foreach ($data as $key => $item){
            /** @var $item Report */

            $mdID = $item->reportMachines[0]->model_description_id;
            $mdName = $item->reportMachines[0]->modelDescription->name;

            foreach($item->features as $k => $feature){
//                dd($feature->value->model_description_id, $mdID, $feature->feature->isIntegerField());
//                if($feature->value->model_description_id == $mdID && $feature->feature->isIntegerField()){
                if($feature->value->model_description_name == $mdName && $feature->feature->isIntegerField()){
                    $arr[$mdID]['name'] = $feature->value->model_description_name;
                    $arr[$mdID]['model_description_id'] = $mdID;
                    $arr[$mdID][$feature->feature->id]['value'][$count] = $feature->value->value;
                    $arr[$mdID][$feature->feature->id]['name'] = $feature->feature->current->name ?? null;
                    $arr[$mdID][$feature->feature->id]['feature_id'] = $feature->feature->id;
                    $count++;
                }
            }
        }

        foreach ($arr ?? [] as $md_id => $item){
            $newData = [];
            $count = 0;
            foreach ($item ?? [] as $feature_id => $itemValue){

                if($feature_id != 'name' && $feature_id != 'model_description_id'){

                    $newData['data'][$count]['value'] = $this->avg($itemValue['value']);
                    $newData['data'][$count]['name'] = $itemValue['name'];
                    $newData['data'][$count]['feature_id'] = $itemValue['feature_id'];

                } else {
                    $newData[$feature_id] = $itemValue;
                }
                $count++;
            }

            $temp = $arr[$md_id];

            $arr[$md_id] = null;
            $arr[$md_id]['name'] = $temp['name'];
            $arr[$md_id]['model_description_id'] = $temp['model_description_id'];
            $arr[$md_id]['data'] = array_values($newData['data']);
        }

        return array_values($arr);
    }

    private function avg(array $data)
    {
        $count = count($data);
        $total = 0;

        array_map(function($v) use (&$total) {
            $total += $this->normalizeValue($v);
        },$data);

        $avg = 0;
        if($count != 0){
            $avg = $total / $count;
        }

        return [
            'count' => $count,
            'avg' => round($avg,2)
        ];
    }

    public function normalizeValue($value)
    {
        // если такое значение 70-90
        if(stripos($value, '-')){
            $avg = 0;
            $temp = explode('-', $value);

            array_map(function($v) use (&$avg) {
                $avg += normalizeNumeric(trim($v));
            }, $temp);

            $value = $avg / count($temp);
        }

        // если такое значение 650/60R34
        if(stripos($value, '/')){
            $value = trim(current(explode('/', $value)));
        }

        return normalizeNumeric($value);
    }
}
