<?php

namespace App\Http\Controllers\Api\Admin\Statistics;

use App\Http\Request\Statistic\RequestReportCount;
use App\Models\Report\Report;
use App\Services\Telegram\TelegramDev;
use App\Http\Request\Statistic;

class SizeController extends BaseStatisticController
{

    /**
     * @SWG\Get(
     *     path="/api/statistic/size/filter",
     *     summary="Получение size для фильтров статистики по size",
     *     tags={"Statistic size"},
     *     security={{"passport": {}}},
     *     @SWG\Parameter(ref="#/parameters/Auth"),
     *
     *     @SWG\Parameter(name="year", in="query", description="год", required=true, type="string"),
     *     @SWG\Parameter(name="status", in="query", required=true, type="string",
     *          description="статус (1 - созданые отчета, 2- открыты для редактирования, 3- отредактированые, 4 - в процессе создания, 5 - верефицирован)"
     *     ),
     *     @SWG\Parameter(name="country", in="query", required=true, type="string",
     *          description="страна (через запятую, если несколько)"
     *     ),
     *     @SWG\Parameter(name="dealer", in="query", required=true, type="string",
     *          description="id дилера (через запятую, если несколько)"
     *     ),
     *     @SWG\Parameter(name="eg", in="query", description="id eg", required=true, type="string"),
     *     @SWG\Parameter(name="md", in="query", description="ID md", required=true, type="string"),
     *
     *     @SWG\Response(response=200, description="Получение отчета"),
     *     @SWG\Response(response="default", description="Ошибка валидации",
     *         @SWG\Schema(
     *            @SWG\Property(property="data", type="object", ref="#/definitions/ErrorMessage"),
     *         )
     *     )
     * )
     */
    public function filterSize(RequestReportCount$request)
    {
        try {
            $year = $request['year'];
            $status = $this->requestStatusData($request['status']);
            $country = $this->requestCountryData($request['country'], $year, $status);
            $dealer = $this->requestDeaelrData($request['dealer'], $year, $status, $country);
            $eg = $this->requestEgData($request['eg'], $dealer, $status, $year);
            $md = $this->requestMdData($request['md'], $dealer, $eg, $status, $year);

            return $this->successJsonMessage($this->sizeData($year, $status, $dealer, $eg, $md));
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    protected function sizeData($year, $status, $dealer, $eg, $md): array
    {
        $models = Report::query()
            ->with([
                'user.dealer',
                'reportMachines.equipmentGroup',
                'reportMachines.modelDescription.product'
            ])
            ->whereYear('created_at', $year)
            ->whereIn('status', $status)
            ->whereHas('user', function($q) use ($dealer){
                $q->whereIn('dealer_id', $dealer);
            })
            ->whereHas('reportMachines', function($q) use ($eg, $md){
                $q->whereIn('equipment_group_id', $eg)
                    ->whereHas('modelDescription.product', function ($q) {
                        $q->whereNotNull('size_name');
                    })
                    ->whereIn('model_description_id', $md);
                ;
            })
            ->get()
            ->toArray()
        ;

        $tmp = [];
        foreach ($models as $model){
            if(isset($model['report_machines'][0]['equipment_group'])){
                if(isset($tmp[$model['report_machines'][0]['model_description']['product']['id']])){
                    $tmp[$model['report_machines'][0]['model_description']['product']['id']]['count'] += 1;
                } else {
                    $tmp[$model['report_machines'][0]['model_description']['product']['id']]['name'] = $model['report_machines'][0]['model_description']['product']['size_name'];
                    $tmp[$model['report_machines'][0]['model_description']['product']['id']]['count'] = 1;
                }
            }
        }

        return $this->finalView($tmp);
    }


//    public function filterSize(Statistic\Report\FilterMd $request)
//    {
//        try {
//            $year = $request['year'];
//            $status = $this->requestStatusData($request['status']);
//            $country = $this->requestCountryData($request['country'], $year, $status);
//            $dealer = $this->requestDeaelrData($request['dealer'], $year, $status, $country);
//            $eg = $this->requestEgData($request['eg'], $dealer, $status, $year);
//
//            return $this->successJsonMessage($this->sizeData($year, $status, $dealer, $eg));
//        } catch (\Exception $error){
//            TelegramDev::error(__FILE__, $error);
//            return $this->errorJsonMessage($error->getMessage());
//        }
//    }
//
//    protected function sizeData($year, $status, $dealer, $eg): array
//    {
//        $models = Report::query()
//            ->with([
//                'user.dealer',
//                'reportMachines.equipmentGroup',
//                'reportMachines.modelDescription.product'
//            ])
//            ->whereYear('created_at', $year)
//            ->whereIn('status', $status)
//            ->whereHas('user', function($q) use ($dealer){
//                $q->whereIn('dealer_id', $dealer);
//            })
//            ->whereHas('reportMachines', function($q) use ($eg){
//                $q->whereIn('equipment_group_id', $eg)
//                    ->whereHas('modelDescription.product', function ($q) {
//                        $q->whereNotNull('size_name');
//                    });
//            })
//            ->get()
//            ->toArray()
//        ;
//
//        $tmp = [];
//        foreach ($models as $model){
//            if(isset($model['report_machines'][0]['equipment_group'])){
//                if(isset($tmp[$model['report_machines'][0]['model_description']['product']['id']])){
//                    $tmp[$model['report_machines'][0]['model_description']['product']['id']]['count'] += 1;
//                } else {
//                    $tmp[$model['report_machines'][0]['model_description']['product']['id']]['name'] = $model['report_machines'][0]['model_description']['product']['size_name'];
//                    $tmp[$model['report_machines'][0]['model_description']['product']['id']]['count'] = 1;
//                }
//            }
//        }
//
//        return $this->finalView($tmp);
//    }
//

//    public function filterMd(Statistic\Size\FilterMD $request)
//    {
//        try {
//            $year = $request['year'];
//            $status = $this->requestStatusData($request['status']);
//            $country = $this->requestCountryData($request['country'], $year, $status);
//            $dealer = $this->requestDeaelrData($request['dealer'], $year, $status, $country);
//            $eg = $this->requestEgData($request['eg'], $dealer, $status, $year);
//            $size = $this->requestSizeData($request['size'], $year, $status, $dealer, $eg);
//
//            return $this->successJsonMessage($this->mdData($year, $status, $dealer, $eg, $size));
//        } catch (\Exception $error){
//            TelegramDev::error(__FILE__, $error);
//            return $this->errorJsonMessage($error->getMessage());
//        }
//    }
//
    protected function mdData(
        $dealer,
        $eg,
        $status,
        $year
    )
    {
//        dd($year, $dealer);
        $reports = Report::query()
            ->with([
                'user.dealer',
                'reportMachines.equipmentGroup',
                'reportMachines.modelDescription.product'
            ])
            ->whereYear('created_at', $year)
            ->whereIn('status', $status)
            ->whereHas('user', function($q) use ($dealer){
                $q->whereIn('dealer_id', $dealer);
            })
            ->whereHas('reportMachines', function($q) use ($eg){
//                dd($size);
                $q->whereIn('equipment_group_id', $eg)
                    ->whereHas('modelDescription.product', function ($q) {
                        $q->whereNotNull('size_name');
                });
            })
            ->get()
            ->toArray()
        ;

        $temp = [];
        foreach ($reports as $report){
            if(isset($report['report_machines'][0]['equipment_group'])){
                if(isset($temp[$report['report_machines'][0]['model_description']['id']])){
                    $temp[$report['report_machines'][0]['model_description']['id']]['count'] += 1;
                } else {
                    $temp[$report['report_machines'][0]['model_description']['id']]['name'] = $report['report_machines'][0]['model_description']['name'];
                    $temp[$report['report_machines'][0]['model_description']['id']]['count'] = 1;
                }
            }
        }

        return $this->finalView($temp);
    }

    /**
     * @SWG\Get(
     *     path="/api/statistic/size/reports",
     *     summary="Получение данных для статистики по size",
     *     tags={"Statistic size"},
     *     security={{"passport": {}}},
     *     @SWG\Parameter(ref="#/parameters/Auth"),
     *
     *     @SWG\Parameter(name="year", in="query", description="год", required=true, type="string"),
     *     @SWG\Parameter(name="status", in="query", required=true, type="string",
     *          description="статус (1 - созданые отчета, 2- открыты для редактирования, 3- отредактированые, 4 - в процессе создания, 5 - верефицирован)"
     *     ),
     *     @SWG\Parameter(name="country", in="query", required=true, type="string",
     *          description="страна (через запятую, если несколько)"
     *     ),
     *     @SWG\Parameter(name="dealer", in="query", required=true, type="string",
     *          description="id дилера (через запятую, если несколько)"
     *     ),
     *     @SWG\Parameter(name="eg", in="query", description="ID eg", required=true, type="string"),
     *     @SWG\Parameter(name="md", in="query", description="ID md", required=true, type="string"),
     *     @SWG\Parameter(name="size", in="query", description="ID size", required=true, type="string"),
     *
     *     @SWG\Response(response=200, description="Получение отчета"),
     *     @SWG\Response(response="default", description="Ошибка валидации",
     *         @SWG\Schema(
     *            @SWG\Property(property="data", type="object", ref="#/definitions/ErrorMessage"),
     *         )
     *     )
     * )
     */
    public function forReports(Statistic\Size\Report $request)
    {
        try {
            $year = $request['year'];
            $status = $this->requestStatusData($request['status']);
            $country = $this->requestCountryData($request['country'], $year, $status);
            $dealer = $this->requestDeaelrData($request['dealer'], $year, $status, $country);
            $eg = $this->requestEgData($request['eg'], $dealer, $status, $year);
            $md = $this->requestMdData($request['md'], $dealer, $eg, $status, $year);
            $size = $this->requestSizeData($request['size'], $year, $status, $dealer, $eg, $md);

            $reports = Report::query()
                ->with([
                    'user.dealer',
                    'reportMachines.equipmentGroup',
                    'reportMachines.modelDescription.product'
                ])
                ->whereYear('created_at', $year)
                ->whereIn('status', $status)
                ->whereHas('user', function($q) use ($dealer){
                    $q->whereIn('dealer_id', $dealer);
                })
                ->whereHas('reportMachines', function($q) use ($eg){
                    $q->whereIn('equipment_group_id', $eg);
                })
                ->whereHas('reportMachines', function($q) use ($md){
                    $q->whereIn('model_description_id', $md);
                })
                ->whereHas('reportMachines.modelDescription.product', function($q) use ($size){
                    $q->whereIn('id', $size);
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

            return $this->successJsonMessage($finalTemp);
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

//    protected function requestSIZEMdData(string $value, $year, $status, $dealer, $eg, $size): array
//    {
//        if($value === self::ALL){
//            return array_flip($this->mdData($year, $status, $dealer, $eg, $size));
//        }
//        return parseParamsByComa($value);
//    }

    // todo , это функция есть и в другом контроллере, нужно оптимизировать
    protected function countryData($year, $status)
    {
        $temp = [];
        $data = \DB::table('reports')
            ->join('users', 'users.id', '=', 'reports.user_id')
            ->join('jd_dealers', 'users.dealer_id', '=', 'jd_dealers.id')
            ->selectRaw('jd_dealers.country')
            ->selectRaw('COUNT(*) as count_reports')
            ->whereYear('reports.created_at', $year)
            ->whereIn('reports.status', $status)
            ->groupByRaw('jd_dealers.country')
            ->get();

        foreach ($data as $item){
            $temp[$item->country] = $this->prettyCountry($item->country) . ' (' . $item->count_reports . ')';
        }

        return $temp;
    }

    public function dealerData($year, $status, $country)
    {
        $data = \DB::table('reports')
            ->join('users', 'users.id', '=', 'reports.user_id')
            ->join('jd_dealers', 'users.dealer_id', '=', 'jd_dealers.id')
            ->selectRaw('jd_dealers.country')
            ->selectRaw('COUNT(*) as count_reports')
            ->selectRaw('GROUP_CONCAT(DISTINCT jd_dealers.id) as dealers')
            ->whereYear('reports.created_at', $year)
            ->whereIn('reports.status', $status)
            ->whereIn('jd_dealers.country', $country)
            ->groupByRaw('jd_dealers.country')
            ->get();


        $dealerIds = '';
        $data->each(function($item) use (&$dealerIds){
            $dealerIds .=  ',' . $item->dealers;
        });

        $dealerIds = array_values(array_diff(explode(',', $dealerIds), array('')));

        // подсчет кол-ва репортов
        $counts = \DB::table('reports')
            ->join('users', 'users.id', '=', 'reports.user_id')
            ->join('jd_dealers', 'users.dealer_id', '=', 'jd_dealers.id')
            ->selectRaw('jd_dealers.name, jd_dealers.id')
            ->selectRaw('COUNT(*) as count')
            ->whereYear('reports.created_at', $year)
            ->whereIn('reports.status', $status)
            ->whereIn('jd_dealers.id', $dealerIds)
            ->groupByRaw('jd_dealers.id')
            ->get();

        $dealers = [];
        foreach ($counts as $item){
            $dealers[$item->id] = $item->name . ' (' . $item->count . ')';
        }

        return $dealers;
    }

    protected function egData($dealer, $status, $year)
    {
        $reports = Report::query()
            ->with([
                'user.dealer',
                'reportMachines.equipmentGroup',
                'reportMachines.modelDescription'
            ])
            ->whereYear('created_at', $year)
            ->whereIn('status', $status)
            ->whereHas('user', function($q) use ($dealer){
                $q->whereIn('dealer_id', $dealer);
            })->get()
            ->toArray()
        ;

        $temp = [];
        foreach ($reports as $report){
            if(isset($report['report_machines'][0]['equipment_group'])){
                if(isset($temp[$report['report_machines'][0]['equipment_group']['id']])){
                    $temp[$report['report_machines'][0]['equipment_group']['id']]['count'] += 1;
                } else {
                    $temp[$report['report_machines'][0]['equipment_group']['id']]['name'] = $report['report_machines'][0]['equipment_group']['name'];
                    $temp[$report['report_machines'][0]['equipment_group']['id']]['count'] = 1;
                }
            }
        }

        return $this->finalView($temp);
    }
}



