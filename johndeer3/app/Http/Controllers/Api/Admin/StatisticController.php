<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Request\Statistic;
use App\Models\JD\Dealer;
use App\Models\JD\EquipmentGroup;
use App\Models\JD\ModelDescription;
use App\Models\Report\Location;
use App\Models\Report\Report;
use App\Repositories\JD\EquipmentGroupRepository;
use App\Repositories\Report\LocationRepository;
use App\Repositories\ReportRepository;
use App\Resources\Report\ReportListStatisticResource;
use App\Services\StatisticService;
use App\Services\Statistics\StatisticFilterService;
use App\Services\Telegram\TelegramDev;
use App\Type\ReportStatus;
use Illuminate\Database\Eloquent\Builder;

class StatisticController extends ApiController
{
    const ALL = 'all';

    private $reportRepository;
    private $statisticService;
    private $equipmentGroupRepository;
    private $reportLocationRepository;
    private $statisticFilterService;

    private $reportStatuses = [
        ReportStatus::CREATED,
        ReportStatus::EDITED,
        ReportStatus::VERIFY,
    ];

    public function __construct(
        ReportRepository $reportRepository,
        StatisticService $statisticService,
        LocationRepository $reportLocationRepository,
        EquipmentGroupRepository $equipmentGroupRepository,
        StatisticFilterService $statisticFilterService
    )
    {
        $this->reportRepository = $reportRepository;
        $this->statisticService = $statisticService;
        $this->reportLocationRepository = $reportLocationRepository;
        $this->equipmentGroupRepository = $equipmentGroupRepository;
        $this->statisticFilterService = $statisticFilterService;
        parent::__construct();
    }

    /**
     * @SWG\Get(
     *     path="/api/statistic/filter/country",
     *     summary="Получение стран для фильтров статистики",
     *     tags={"Statistic"},
     *     security={{"passport": {}}},
     *     @SWG\Parameter(ref="#/parameters/Auth"),
     *
     *     @SWG\Parameter(name="year", in="query", description="год", required=true, type="string"),
     *
     *     @SWG\Response(response=200, description="Получение отчета"),
     *     @SWG\Response(response="default", description="Ошибка валидации",
     *         @SWG\Schema(
     *              @SWG\Property(property="data", type="object", ref="#/definitions/ErrorMessage"),
     *         )
     *     )
     * )
     */
    public function filterCountry(Statistic\FilterCountry $request)
    {
        try {
            $year = $request['year'];

            return $this->successJsonMessage(
//                $this->statisticFilterService->reportCountryData($year),
                $this->countryData($year)
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    private function countryData($year)
    {
        $countries = $this->reportLocationRepository->getListByFilter(Location::TYPE_COUNTRY_FILTER, null);
        $countries = array_reverse($countries);

        foreach ($countries ?? [] as $item){
            $count = $this->reportLocationRepository
                ->countReportByCountryAndYear($item, $year, $this->reportStatuses);
            $countries[$item] = $item . " ({$count})";
        }

        return $countries;
    }

    /**
     * @SWG\Get(
     *     path="/api/statistic/filter/dealer",
     *     summary="Получение стран для фильтров статистики",
     *     tags={"Statistic"},
     *     security={{"passport": {}}},
     *     @SWG\Parameter(ref="#/parameters/Auth"),
     *
     *     @SWG\Parameter(name="year", in="query", description="год", required=true, type="string"),
     *     @SWG\Parameter(name="country", in="query", required=true, type="string",
     *          description="страна (через запятую, если несколько)"
     *     ),
     *
     *     @SWG\Response(response=200, description="Получение отчета"),
     *     @SWG\Response(response="default", description="Ошибка валидации",
     *         @SWG\Schema(
     *            @SWG\Property(property="data", type="object", ref="#/definitions/ErrorMessage"),
     *         )
     *     )
     * )
     */
    public function filterDealer(Statistic\FilterDealer $request)
    {
        try {
            $year = $request['year'];
            $country = $this->requestCountryData($request['country'], $year);

            return $this->successJsonMessage(
//                $this->statisticFilterService->reportDealerData($year, $country)
                $this->dealerData($year, $country)
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    private function requestCountryData($data, $year)
    {
        if($data === self::ALL){
            $country = array_flip($this->countryData($year));
        } else {
            $country = parseParamsByComa($data);
        }

        return $country;
    }

    private function dealerData($year, $country)
    {
        $dealers = Dealer::query()->active()->with('users_ps')
            ->select(['id', 'name'])
            ->with(['users_ps.reports'])
            ->whereHas('users_ps', function($q){
                $q->has('reports');
            })
            ->get()
        ;

        $temp = [];
        foreach ($dealers ?? [] as $id => $dealer){
            $count = 0;
            foreach ($dealer->users_ps as $item){
                $c = $item->reports()
                    ->whereIn('status', $this->reportStatuses)
                    ->whereYear('created_at', $year)
                    ->whereHas('location', function (Builder $q) use($country) {
                        $q->whereIn('country', $country);
                    })
                    ->count();

                $count += $c;
            }

            $temp[$dealer->id] = $dealer->name . " ({$count})";
        }

        return $temp;
    }

    /**
     * @SWG\Get(
     *     path="/api/statistic/filter/eg",
     *     summary="Получение eg для фильтров статистики",
     *     tags={"Statistic"},
     *     security={{"passport": {}}},
     *     @SWG\Parameter(ref="#/parameters/Auth"),
     *
     *     @SWG\Parameter(name="year", in="query", description="год", required=true, type="string"),
     *     @SWG\Parameter(name="country", in="query", required=true, type="string",
     *          description="страна (через запятую, если несколько)"
     *     ),
     *     @SWG\Parameter(name="dealer", in="query", required=true, type="string",
     *          description="id дилера (через запятую, если несколько)"
     *     ),
     *
     *     @SWG\Response(response=200, description="Получение отчета"),
     *     @SWG\Response(response="default", description="Ошибка валидации",
     *         @SWG\Schema(
     *            @SWG\Property(property="data", type="object", ref="#/definitions/ErrorMessage"),
     *         )
     *     )
     * )
     */
    public function filterEg(Statistic\FilterEg $request)
    {
        try {
            $year = $request['year'];
            $country = $this->requestCountryData($request['country'], $year);
            $dealer = $this->requestDealerData($request['dealer'], $country, $year);

            TelegramDev::info("📊 Запрос на EG для статистики");

            $egs = EquipmentGroup::query()
                ->withCount(['reportMachines'])
                ->where('for_statistic', true)
                ->get()
            ;

            $temp = [];
            foreach ($egs as $eg){

                $c = $eg->reportMachines()
                    ->with('reports')
                    ->whereHas('reports', function($q) use($year, $country, $dealer) {
                        $q->whereIn('status', $this->reportStatuses)
                            ->whereYear('created_at', $year)
                            ->whereHas('location', function (Builder $q) use($country) {
                                $q->whereIn('country', $country);
                            })->whereHas('user', function($q) use($dealer) {
                                $q->whereIn('dealer_id', $dealer);
                            });
                    })
                    ->count();

                $temp[$eg->id] = $eg->name . " ({$c})";
            }

            return $this->successJsonMessage($temp);
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    private function requestDealerData($data, $country, $year)
    {
        if($data === self::ALL){
            $dealer = array_flip($this->dealerData($year, $country));
        } else {
            $dealer = parseParamsByComa($data);
        }

        return $dealer;
    }

    /**
     * @SWG\Get(
     *     path="/api/statistic/filter/md",
     *     summary="Получение md для фильтров статистики",
     *     tags={"Statistic"},
     *     security={{"passport": {}}},
     *     @SWG\Parameter(ref="#/parameters/Auth"),
     *
     *     @SWG\Parameter(name="year", in="query", description="год", required=true, type="string"),
     *     @SWG\Parameter(name="country", in="query", required=true, type="string",
     *          description="страна (через запятую, если несколько)"
     *     ),
     *     @SWG\Parameter(name="dealer", in="query", required=true, type="string",
     *          description="id дилера (через запятую, если несколько)"
     *     ),
     *     @SWG\Parameter(name="eg", in="query", description="id eg", required=true, type="string"),
     *
     *     @SWG\Response(response=200, description="Получение отчета"),
     *     @SWG\Response(response="default", description="Ошибка валидации",
     *         @SWG\Schema(
     *            @SWG\Property(property="data", type="object", ref="#/definitions/ErrorMessage"),
     *         )
     *     )
     * )
     * @throws \Exception
     */
    public function filterMd(Statistic\FilterMd $request)
    {
        try {
            $year = $request['year'];

            $country = $this->requestCountryData($request['country'], $year);

            $dealer = $this->requestDealerData($request['dealer'], $country, $year);
            $eg = parseParamsByComa($request['eg']);
            $egModel = $this->equipmentGroupRepository->getBy('id', $eg);

            TelegramDev::info("📊 Запрос на MD для статистики");

            $mds = ModelDescription::query()
                ->with(['reportMachine.reports'])
                ->withCount(['reportMachine'])
                ->where('eg_jd_id', $egModel->jd_id)
                ->get();

            $temp = [];
            foreach ($mds as $md){

                $c = $md->reportMachine()
                    ->with('reports')
                    ->whereHas('reports', function($q) use($year, $country, $dealer) {
                        $q->whereIn('status', $this->reportStatuses)
                            ->whereYear('created_at', $year)
                            ->whereHas('location', function (Builder $q) use($country) {
                                $q->whereIn('country', $country);
                            })->whereHas('user', function($q) use($dealer) {
                                $q->whereIn('dealer_id', $dealer);
                            });
                    })
                    ->count();

                if($c > 0){
                    $temp[$md->id] = $md->name . " ({$c})";
                }

            }

            return $this->successJsonMessage($temp);
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @SWG\Get(
     *     path="/api/statistic/machines",
     *     summary="Получение данных по статистике для техники (№1)",
     *     tags={"Statistic"},
     *     security={{"passport": {}}},
     *     @SWG\Parameter(ref="#/parameters/Auth"),
     *
     *     @SWG\Parameter(name="year", in="query", description="год", required=true, type="string"),
     *     @SWG\Parameter(name="country", in="query", required=true, type="string",
     *          description="страна (через запятую, если несколько)"
     *     ),
     *     @SWG\Parameter(name="dealerId", in="query", required=true, type="string",
     *          description="id дилера (через запятую, если несколько)"
     *     ),
     *     @SWG\Parameter(name="eg", in="query", description="id eg", required=true, type="string"),
     *     @SWG\Parameter(name="md", in="query", required=true, type="string",
     *          description="id modelDescription (через запятую, если несколько)"
     *     ),
     *
     *     @SWG\Response(response=200, description="Получение отчета"),
     *     @SWG\Response(response="default", description="Ошибка валидации",
     *         @SWG\Schema(
     *            @SWG\Property(property="data", type="object", ref="#/definitions/ErrorMessage"),
     *         )
     *     )
     * )
     * @throws \Exception
     */
    public function forMachines(Statistic\RequestMachineStatistic $request)
    {
        try {
            TelegramDev::info("Запрос на статистику №1");

            $reports = $this->reportRepository->getForStatistic(
                $request['dealerId'],
                $request['eg'],
                $request['md'],
                $request['country'],
                $request['year']
            );

            return $this->successJsonMessage($this->statisticService->statisticMachine($reports));
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @SWG\Get(
     *     path="/api/statistic/machine",
     *     summary="Получение данных по статистике для техники (№2)",
     *     tags={"Statistic"},
     *     security={{"passport": {}}},
     *     @SWG\Parameter(ref="#/parameters/Auth"),
     *
     *     @SWG\Parameter(name="year", in="query", description="год", required=true, type="string"),
     *     @SWG\Parameter(name="country", in="query", required=true, type="string",
     *          description="страна (через запятую, если несколько)"
     *     ),
     *     @SWG\Parameter(name="dealerId", in="query", required=true, type="string",
     *          description="id дилера (через запятую, если несколько)"
     *     ),
     *     @SWG\Parameter(name="eg", in="query", description="id eg", required=true, type="string"),
     *     @SWG\Parameter(name="md", in="query", required=true, type="string",
     *          description="id modelDescription"
     *     ),
     *
     *     @SWG\Response(response=200, description="Получение отчета",
     *         @SWG\Schema(ref="#/definitions/ReportListStatisticResource")
     *     ),
     *     @SWG\Response(response="default", description="Ошибка валидации",
     *         @SWG\Schema(
     *            @SWG\Property(property="data", type="object", ref="#/definitions/ErrorMessage")
     *         )
     *     )
     * )
     * todo добавить фидьтр для all и написать под них тест
     * @throws \Exception
     */
    public function forMachine(Statistic\RequestMachineStatistic $request)
    {
        try {
            TelegramDev::info("Запрос на статистику №2");

//            $country = $this->requestCountryData($request['country'], $request['year']);
//            $dealer = $this->requestDealerData($request['dealerId'], $country, $request['year']);

            $reports = $this->reportRepository->getForStatistic(
                $request['dealerId'],
                $request['eg'],
                $request['md'],
                $request['country'],
                $request['year']
            );

            return ReportListStatisticResource::collection($reports);
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @SWG\Get(
     *     path="/api/statistic/types",
     *     summary="Получение данных по типу комбайна (для статистики)",
     *     tags={"Statistic"},
     *     security={{"passport": {}}},
     *     @SWG\Parameter(ref="#/parameters/Auth"),
     *
     *     @SWG\Parameter(name="year", in="query", description="год", required=true, type="string"),
     *     @SWG\Parameter(name="country", in="query", required=true, type="string",
     *          description="страна (через запятую, если несколько)"
     *     ),
     *     @SWG\Parameter(name="dealerId", in="query", required=true, type="string",
     *          description="id дилера (через запятую, если несколько)"
     *     ),
     *     @SWG\Parameter(name="eg", in="query", description="id eg", required=true, type="string"),
     *     @SWG\Parameter(name="md", in="query", required=true, type="string",
     *          description="id modelDescription"
     *     ),
     *
     *     @SWG\Response(response=200, description="Получение отчета",
     *         @SWG\Schema(ref="#/definitions/ReportListStatisticResource")
     *     ),
     *     @SWG\Response(response="default", description="Ошибка валидации",
     *         @SWG\Schema(
     *            @SWG\Property(property="data", type="object", ref="#/definitions/ErrorMessage")
     *         )
     *     )
     * )
     * @throws \Exception
     */
    public function forTypes(Statistic\RequestMachineStatistic $request)
    {
        try {
            TelegramDev::info("Запрос на статистику №2");

            $reports = $this->reportRepository->getForStatistic(
                $request['dealerId'],
                $request['eg'],
                $request['md'],
                $request['country'],
                $request['year']
            );

            return ReportListStatisticResource::collection($reports);
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }


    /**
     * @throws \Exception
     */
    public function forReports(Statistic\RequestReportCount $request)
    {
        try {
            TelegramDev::info("Запрос на статистику №3");

            $year = $request['year'];
            $status = $request['status'];

            $c = \DB::table('reports')
                ->join('users', 'users.id', '=', 'reports.user_id')
                ->join('jd_dealers', 'users.dealer_id', '=', 'jd_dealers.id')
                ->selectRaw('jd_dealers.country')
                ->selectRaw('COUNT(*) as count_reports')
                ->selectRaw('GROUP_CONCAT(DISTINCT jd_dealers.id) as dealers')
                ->whereYear('reports.created_at', $year)
                ->where('reports.status', $status)
                ->groupByRaw('jd_dealers.country')
                ->get();

            $dealerIds = '';
            $c->each(function($item) use (&$dealerIds){
                $dealerIds .=  ',' . $item->dealers;
            });
            $dealerIds = array_values(array_diff(explode(',', $dealerIds), array('')));

            $temp = [];
            foreach ($dealerIds as $id){
                $reports = Report::query()
                    ->with([
                        'user.dealer',
                        'reportMachines.equipmentGroup',
                        'reportMachines.modelDescription'
                    ])
                    ->whereHas('user', function($q) use ($id){
                        $q->where('dealer_id', $id);
                    })->get()
                    ->toArray()
                ;

                $temp[$id] = [];
                $temp[$id]['total'] = count($reports);
                $temp[$id]['dealer']['name'] = $reports[0]['user']['dealer']['name'] ?? null;
                $temp[$id]['dealer']['id'] = $reports[0]['user']['dealer']['id'] ?? null;
                $temp[$id]['machine'] = [];
                foreach ($reports as $item){
                    if(isset($item['report_machines'][0]['equipment_group']['name']) && isset($item['report_machines'][0]['model_description']['name'])){
                        if(isset($temp[$id]['machine'][$item['report_machines'][0]['equipment_group']['name']][$item['report_machines'][0]['model_description']['name']])){
                            $temp[$id]['machine'][$item['report_machines'][0]['equipment_group']['name']][$item['report_machines'][0]['model_description']['name']] += 1;
                        } else {
                            $temp[$id]['machine'][$item['report_machines'][0]['equipment_group']['name']][$item['report_machines'][0]['model_description']['name']] = 1;
                        }

                        if(isset($temp[$id]['machine'][$item['report_machines'][0]['equipment_group']['name']]['total'])){
                            $temp[$id]['machine'][$item['report_machines'][0]['equipment_group']['name']]['total'] += 1;
                        } else {
                            $temp[$id]['machine'][$item['report_machines'][0]['equipment_group']['name']]['total'] = 1;
                        }
                    }
                }
            }

            $data = [];
            foreach ($c as $k => $i){
                $data[$k]['country'] = $i->country;
                $data[$k]['total'] = $i->count_reports;
                foreach (explode(',', $i->dealers) as $key => $item){
                    $data[$k]['data'][$key] = $temp[$item];
                }
            }

            return $this->successJsonMessage($data);
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }
}

