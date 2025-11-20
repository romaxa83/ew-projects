<?php

namespace App\Services\Statistics;

use App\DTO\Stats\StatsDto;
use App\Models\Report\Report;

class StatisticReportService extends StatisticFilterService
{
    public function reportCount(StatsDto $dto): array
    {
        $dto = $this->swapDtoForReport($dto);

        $reports = Report::query()
            ->with([
                'user.dealer',
                'reportMachines.equipmentGroup',
                'reportMachines.modelDescription'
            ])
            ->whereYear('created_at', $dto->year)
            ->whereIn('status', $dto->status)
            ->whereHas('user', function($q) use ($dto){
                $q->whereIn('dealer_id', $dto->dealer);
            })
            ->whereHas('reportMachines', function($q) use ($dto){
                $q->whereIn('equipment_group_id', $dto->eg);
            })
            ->whereHas('reportMachines', function($q) use ($dto){
                $q->whereIn('model_description_id', $dto->md);
            })
            ->get()
            ->toArray()
        ;

        $temp = [];

        foreach ($reports as $i => $item){
            $country = $this->prettyCountry($item['user']['dealer']['country']);

            $temp['countries'][$country][$item['user']['dealer']['id']] = $item['user']['dealer']['name'];

            if(isset($item['report_machines'][0]['model_description']['id'])){
                $temp['data'][$item['report_machines'][0]['model_description']['id']]['name'] = $item['report_machines'][0]['model_description']['name'];
                $temp['data'][$item['report_machines'][0]['model_description']['id']]['eg']['id'] = $item['report_machines'][0]['equipment_group']['id'];
                $temp['data'][$item['report_machines'][0]['model_description']['id']]['eg']['name'] = $item['report_machines'][0]['equipment_group']['name'];
                $temp['data'][$item['report_machines'][0]['model_description']['id']]['values'][$item['user']['dealer']['id']]['name'] = $item['user']['dealer']['name'];
                if(isset($temp['data'][$item['report_machines'][0]['model_description']['id']]['values'][$item['user']['dealer']['id']]['val'])){
                    $temp['data'][$item['report_machines'][0]['model_description']['id']]['values'][$item['user']['dealer']['id']]['val'] += 1;
                } else {
                    $temp['data'][$item['report_machines'][0]['model_description']['id']]['values'][$item['user']['dealer']['id']]['val'] = 1;
                }
            }
        }

        $dealersList = [];
        foreach ($temp['countries'] ?? [] as $d){
            $dealersList = $dealersList + $d;
        }

        $tempF = [];
        $countCountries = 0;
        foreach ($temp['countries'] ?? [] as $con => $v){
            $tempF['countries'][$countCountries]['name'] = $con;
            $tempF['countries'][$countCountries]['dealers_count'] = count($v);
            $countCountries++;
        }

        $count = 0;
        foreach ($temp['data'] ?? [] as $key => $value){
            $tempF['data'][$value['eg']['id']][$count]['name'] = $value['name'];
            $tempF['data'][$value['eg']['id']][$count]['id'] = $key;
            $tempF['data'][$value['eg']['id']][$count]['eg'] = $value['eg'];
            $countT = 0;
            foreach ($dealersList ?? [] as $k => $v){
                $tempF['data'][$value['eg']['id']][$count]['values'][$countT]['dealer_name'] = $v;
                if(array_key_exists($k, $value['values'])){
                    $tempF['data'][$value['eg']['id']][$count]['values'][$countT]['value'] = $value['values'][$k]['val'];
                } else {
                    $tempF['data'][$value['eg']['id']][$count]['values'][$countT]['value'] = 0;
                }
                $countT++;
            }
            $count++;
        }

        $finalTemp = [];
        if(!empty($tempF)){
            $finalTemp['countries'] = $tempF['countries'];
            foreach ($tempF['data'] ?? [] as $one){
                foreach ($one ?? [] as $o){
                    $finalTemp['data'][] = $o;
                }
            }
        }

        return $finalTemp;
    }

    public function reportType(StatsDto $dto): array
    {
        $dto = $this->swapDtoForReport($dto);

        $reports = Report::query()
            ->with([
                'user.dealer',
                'reportMachines.equipmentGroup',
                'reportMachines.modelDescription.product'
            ])
            ->whereYear('created_at', $dto->year)
            ->whereIn('status', $dto->status)
            ->whereHas('user', function($q) use ($dto){
                $q->whereIn('dealer_id', $dto->dealer);
            })
            ->whereHas('reportMachines', function($q) use ($dto){
                $q->whereIn('equipment_group_id', $dto->eg);
            })
            ->whereHas('reportMachines', function($q) use ($dto){
                $q->whereIn('model_description_id', $dto->md);
            })
            ->whereHas('reportMachines.modelDescription.product', function($q) use ($dto){
                $q->whereIn('type', $dto->type);
            })
            ->get()
            ->toArray()
        ;

        $temp = [];

        foreach ($reports as $i => $item){
            $country = $this->prettyCountry($item['user']['dealer']['country']);

            $temp['countries'][$country][$item['user']['dealer']['id']] = $item['user']['dealer']['name'];

            if(isset($item['report_machines'][0]['model_description']['id'])){
                $temp['data'][$item['report_machines'][0]['model_description']['id']]['name'] = $item['report_machines'][0]['model_description']['name'];
                $temp['data'][$item['report_machines'][0]['model_description']['id']]['type'] = $item['report_machines'][0]['model_description']['product']['type'];
                $temp['data'][$item['report_machines'][0]['model_description']['id']]['eg']['id'] = $item['report_machines'][0]['equipment_group']['id'];
                $temp['data'][$item['report_machines'][0]['model_description']['id']]['eg']['name'] = $item['report_machines'][0]['equipment_group']['name'];
                $temp['data'][$item['report_machines'][0]['model_description']['id']]['values'][$item['user']['dealer']['id']]['name'] = $item['user']['dealer']['name'];
                if(isset($temp['data'][$item['report_machines'][0]['model_description']['id']]['values'][$item['user']['dealer']['id']]['val'])){
                    $temp['data'][$item['report_machines'][0]['model_description']['id']]['values'][$item['user']['dealer']['id']]['val'] += 1;
                } else {
                    $temp['data'][$item['report_machines'][0]['model_description']['id']]['values'][$item['user']['dealer']['id']]['val'] = 1;
                }
            }
        }

        $dealersList = [];
        foreach ($temp['countries'] ?? [] as $d){
            $dealersList = $dealersList + $d;
        }

        $tempF = [];
        $countCountries = 0;
        foreach ($temp['countries'] ?? [] as $con => $v){
            $tempF['countries'][$countCountries]['name'] = $con;
            $tempF['countries'][$countCountries]['dealers_count'] = count($v);
            $countCountries++;
        }

        $count = 0;
        foreach ($temp['data'] ?? [] as $key => $value){
            $tempF['data'][$value['eg']['id']][$count]['name'] = $value['name'];
            $tempF['data'][$value['eg']['id']][$count]['type'] = $value['type'];
            $tempF['data'][$value['eg']['id']][$count]['id'] = $key;
            $tempF['data'][$value['eg']['id']][$count]['eg'] = $value['eg'];
            $countT = 0;
            foreach ($dealersList ?? [] as $k => $v){
                $tempF['data'][$value['eg']['id']][$count]['values'][$countT]['dealer_name'] = $v;
                if(array_key_exists($k, $value['values'])){
                    $tempF['data'][$value['eg']['id']][$count]['values'][$countT]['value'] = $value['values'][$k]['val'];
                } else {
                    $tempF['data'][$value['eg']['id']][$count]['values'][$countT]['value'] = 0;
                }
                $countT++;
            }
            $count++;
        }

        $finalTemp = [];
        if(!empty($tempF)){
            $finalTemp['countries'] = $tempF['countries'];
            foreach ($tempF['data'] ?? [] as $one){
                foreach ($one ?? [] as $o){
                    $finalTemp['data'][] = $o;
                }
            }
        }

        return $finalTemp;
    }

    public function reportSize(StatsDto $dto): array
    {
        $dto = $this->swapDtoForReport($dto);

        $reports = Report::query()
            ->with([
                'user.dealer',
                'reportMachines.equipmentGroup',
                'reportMachines.modelDescription.product'
            ])
            ->whereYear('created_at', $dto->year)
            ->whereIn('status', $dto->status)
            ->whereHas('user', function($q) use ($dto){
                $q->whereIn('dealer_id', $dto->dealer);
            })
            ->whereHas('reportMachines', function($q) use ($dto){
                $q->whereIn('equipment_group_id', $dto->eg);
            })
            ->whereHas('reportMachines', function($q) use ($dto){
                $q->whereIn('model_description_id', $dto->md);
            })
            ->whereHas('reportMachines.modelDescription.product', function($q) use ($dto){
                $q->whereIn('id', $dto->size);
            })
            ->get()
            ->toArray()
        ;

        $temp = [];

        foreach ($reports as $i => $item){
            $country = $this->prettyCountry($item['user']['dealer']['country']);

            $temp['countries'][$country][$item['user']['dealer']['id']] = $item['user']['dealer']['name'];

            if(isset($item['report_machines'][0]['model_description']['id'])){
                $temp['data'][$item['report_machines'][0]['model_description']['id']]['name'] = $item['report_machines'][0]['model_description']['name'];
                $temp['data'][$item['report_machines'][0]['model_description']['id']]['size_name'] = $item['report_machines'][0]['model_description']['product']['size_name'];
                $temp['data'][$item['report_machines'][0]['model_description']['id']]['eg']['id'] = $item['report_machines'][0]['equipment_group']['id'];
                $temp['data'][$item['report_machines'][0]['model_description']['id']]['eg']['name'] = $item['report_machines'][0]['equipment_group']['name'];
                $temp['data'][$item['report_machines'][0]['model_description']['id']]['values'][$item['user']['dealer']['id']]['name'] = $item['user']['dealer']['name'];
                if(isset($temp['data'][$item['report_machines'][0]['model_description']['id']]['values'][$item['user']['dealer']['id']]['val'])){
                    $temp['data'][$item['report_machines'][0]['model_description']['id']]['values'][$item['user']['dealer']['id']]['val'] += 1;
                } else {
                    $temp['data'][$item['report_machines'][0]['model_description']['id']]['values'][$item['user']['dealer']['id']]['val'] = 1;
                }
            }
        }

        $dealersList = [];
        foreach ($temp['countries'] ?? [] as $d){
            $dealersList = $dealersList + $d;
        }

        $tempF = [];
        $countCountries = 0;
        foreach ($temp['countries'] ?? [] as $con => $v){
            $tempF['countries'][$countCountries]['name'] = $con;
            $tempF['countries'][$countCountries]['dealers_count'] = count($v);
            $countCountries++;
        }

        $count = 0;
        foreach ($temp['data'] ?? [] as $key => $value){

            $tempF['data'][$value['eg']['id']][$count]['name'] = $value['name'];
            $tempF['data'][$value['eg']['id']][$count]['size_name'] = $value['size_name'];
            $tempF['data'][$value['eg']['id']][$count]['id'] = $key;
            $tempF['data'][$value['eg']['id']][$count]['eg'] = $value['eg'];
            $countT = 0;
            foreach ($dealersList ?? [] as $k => $v){
                $tempF['data'][$value['eg']['id']][$count]['values'][$countT]['dealer_name'] = $v;
                if(array_key_exists($k, $value['values'])){
                    $tempF['data'][$value['eg']['id']][$count]['values'][$countT]['value'] = $value['values'][$k]['val'];
                } else {
                    $tempF['data'][$value['eg']['id']][$count]['values'][$countT]['value'] = 0;
                }
                $countT++;
            }
            $count++;
        }

        $finalTemp = [];
        if(!empty($tempF)){
            $finalTemp['countries'] = $tempF['countries'];
            foreach ($tempF['data'] ?? [] as $one){
                foreach ($one ?? [] as $o){
                    $finalTemp['data'][] = $o;
                }
            }
        }

        return $finalTemp;
    }

    public function reportCrop(StatsDto $dto): array
    {
        $dto = $this->swapDtoForReport($dto);

        $reports = Report::query()
            ->with([
                'user.dealer',
                'reportMachines.equipmentGroup',
                'reportMachines.modelDescription.product',
                'features.value.valueCurrent'
            ])
            ->whereYear('created_at', $dto->year)
            ->whereIn('status', $dto->status)
            ->whereHas('user', function($q) use ($dto){
                $q->whereIn('dealer_id', $dto->dealer);
            })
            ->whereHas('reportMachines', function($q) use ($dto){
                $q->whereIn('equipment_group_id', $dto->eg);
            })
            ->whereHas('reportMachines', function($q) use ($dto){
                $q->whereIn('model_description_id', $dto->md);
            })
            ->whereHas('features.value', function($q) use ($dto){
                $q->whereIn('value_id', $dto->crop);
            })
            ->get()
            ->toArray()
        ;

        $temp = [];

        foreach ($reports as $i => $item){
            $country = $this->prettyCountry($item['user']['dealer']['country']);

            $temp['countries'][$country][$item['user']['dealer']['id']] = $item['user']['dealer']['name'];

            if(isset($item['report_machines'][0]['model_description']['id'])){

                $valueName = null;
                foreach (data_get($item, 'features') as $f){
                    if(in_array(data_get($f, 'value.value_id'), $dto->crop)){
                        $valueName = data_get($f, 'value.value_current.name');
                        break;
                    }
                }

                $temp['data'][$item['report_machines'][0]['model_description']['id']]['name'] = $item['report_machines'][0]['model_description']['name'];
                $temp['data'][$item['report_machines'][0]['model_description']['id']]['crop_name'] = $valueName;
                $temp['data'][$item['report_machines'][0]['model_description']['id']]['eg']['id'] = $item['report_machines'][0]['equipment_group']['id'];
                $temp['data'][$item['report_machines'][0]['model_description']['id']]['eg']['name'] = $item['report_machines'][0]['equipment_group']['name'];
                $temp['data'][$item['report_machines'][0]['model_description']['id']]['values'][$item['user']['dealer']['id']]['name'] = $item['user']['dealer']['name'];
                if(isset($temp['data'][$item['report_machines'][0]['model_description']['id']]['values'][$item['user']['dealer']['id']]['val'])){
                    $temp['data'][$item['report_machines'][0]['model_description']['id']]['values'][$item['user']['dealer']['id']]['val'] += 1;
                } else {
                    $temp['data'][$item['report_machines'][0]['model_description']['id']]['values'][$item['user']['dealer']['id']]['val'] = 1;
                }
            }
        }

        $dealersList = [];
        foreach ($temp['countries'] ?? [] as $d){
            $dealersList = $dealersList + $d;
        }

        $tempF = [];
        $countCountries = 0;
        foreach ($temp['countries'] ?? [] as $con => $v){
            $tempF['countries'][$countCountries]['name'] = $con;
            $tempF['countries'][$countCountries]['dealers_count'] = count($v);
            $countCountries++;
        }

        $count = 0;
        foreach ($temp['data'] ?? [] as $key => $value){
            $tempF['data'][$value['eg']['id']][$count]['name'] = $value['name'];
            $tempF['data'][$value['eg']['id']][$count]['crop_name'] = $value['crop_name'];
            $tempF['data'][$value['eg']['id']][$count]['id'] = $key;
            $tempF['data'][$value['eg']['id']][$count]['eg'] = $value['eg'];
            $countT = 0;
            foreach ($dealersList ?? [] as $k => $v){
                $tempF['data'][$value['eg']['id']][$count]['values'][$countT]['dealer_name'] = $v;
                if(array_key_exists($k, $value['values'])){
                    $tempF['data'][$value['eg']['id']][$count]['values'][$countT]['value'] = $value['values'][$k]['val'];
                } else {
                    $tempF['data'][$value['eg']['id']][$count]['values'][$countT]['value'] = 0;
                }
                $countT++;
            }
            $count++;
        }

        $finalTemp = [];
        if(!empty($tempF)){
            $finalTemp['countries'] = $tempF['countries'];
            foreach ($tempF['data'] ?? [] as $one){
                foreach ($one ?? [] as $o){
                    $finalTemp['data'][] = $o;
                }
            }
        }

        return $finalTemp;
    }
}

