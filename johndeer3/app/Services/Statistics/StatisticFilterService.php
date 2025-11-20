<?php

namespace App\Services\Statistics;

use App\DTO\Stats\StatsDto;
use App\Models\JD\Dealer;
use App\Models\JD\EquipmentGroup;
use App\Models\JD\ModelDescription;
use App\Models\Report\Location;
use App\Models\Report\Report;
use App\Models\Report\ReportMachine;
use App\Repositories\JD\EquipmentGroupRepository;
use App\Repositories\Report\LocationRepository;
use App\Type\ReportStatus;
use Illuminate\Database\Eloquent\Builder;

// методы для получения данных для фильтров статистики
class StatisticFilterService
{
    const ALL = 'all';

    public function __construct(
        protected LocationRepository $locationRepository,
        protected EquipmentGroupRepository $equipmentGroupRepository
    )
    {}

    public function machineCountryData(StatsDto $dto): array
    {
        $countries = $this->locationRepository->getListByFilter(Location::TYPE_COUNTRY_FILTER, null);
        $countries = array_reverse($countries);

        foreach ($countries ?? [] as $item){
            $count = $this->locationRepository
                ->countReportByCountryAndYear($item, $dto->year, ReportStatus::listForMachineStatistics());
            $countries[$item] = $item . " ({$count})";
        }

        return $countries;
    }

    public function machineDealerData(StatsDto $dto, $swapDto = true): array
    {
        $dto = $swapDto ? $this->swapDtoForMachine($dto) : $dto;

        $dealers = Dealer::query()
            ->active()
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
                    ->whereIn('status', ReportStatus::listForMachineStatistics())
                    ->whereYear('created_at', $dto->year)
                    ->whereHas('location', function (Builder $q) use($dto) {
                        if(is_array($dto->country)) {
                            return $q->whereIn('country', $dto->country);
                        }
                        return $q->where('country', $dto->country);
                    })
                    ->count();

                $count += $c;
            }

            $temp[$dealer->id] = $dealer->name . " ({$count})";
        }

        return $temp;
    }

    public function machineEgData(StatsDto $dto, $swapDto = true): array
    {
        $dto = $swapDto ? $this->swapDtoForMachine($dto) : $dto;

        $egs = EquipmentGroup::query()
            ->with(['reportMachines.reports'])
            ->withCount(['reportMachines'])
            ->where('for_statistic', true)
            ->get()
        ;

        $temp = [];
        foreach ($egs as $eg){
            /** @var $eg EquipmentGroup */
            $c = $eg->reportMachines()
                ->with('reports')
                ->whereHas('reports', function($q) use($dto) {
                    $q->whereIn('status', ReportStatus::listForMachineStatistics())
                        ->whereYear('created_at', $dto->year)
                        ->whereHas('location', function (Builder $q) use($dto) {
                            if(is_array($dto->country)) {
                                return $q->whereIn('country', $dto->country);
                            }
                            return $q->where('country', $dto->country);
                        })
                        ->whereHas('user', function($q) use($dto) {
                            if(is_array($dto->dealer)) {
                                return $q->whereIn('dealer_id', $dto->dealer);
                            }
                            return $q->where('dealer_id', $dto->dealer);
                        });
                })
                ->count()
            ;

            $temp[$eg->id] = $eg->name . " ({$c})";
        }

        return $temp;
    }

    public function machineMdData(StatsDto $dto, $swapDto = true): array
    {
        $dto = $swapDto ? $this->swapDtoForMachine($dto) : $dto;

        $egModel = $this->equipmentGroupRepository->getBy('id', $dto->eg);

        $mds = ModelDescription::query()
            ->with(['reportMachine.reports'])
            ->withCount(['reportMachine'])
            ->where('eg_jd_id', $egModel->jd_id)
            ->get();

        $temp = [];

        foreach ($mds as $md){
            /** @var $md ModelDescription */
            $c = $md->reportMachine()
                ->with('reports')
                ->whereHas('reports', function($q) use($dto) {
                    $q->whereIn('status', ReportStatus::listForMachineStatistics())
                        ->whereYear('created_at', $dto->year)
                        ->whereHas('location', function (Builder $q) use($dto) {
                            if(is_array($dto->country)) {
                                return $q->whereIn('country', $dto->country);
                            }
                            return $q->where('country', $dto->country);
                        })->whereHas('user', function($q) use($dto) {
                            if(is_array($dto->dealer)) {
                                return $q->whereIn('dealer_id', $dto->dealer);
                            }
                            return $q->where('dealer_id', $dto->dealer);
                        });
                })
                ->count();

            if($c > 0){
                $temp[$md->id] = $md->name . " ({$c})";
            }
        }

        return $temp;
    }

    public function swapDtoForMachine(StatsDto $dto): StatsDto
    {
        if($dto->country && $dto->country == self::ALL){
            $dto->country = array_flip($this->machineCountryData($dto));
        }
        if($dto->dealer && $dto->dealer == self::ALL){
            $dto->dealer = array_flip($this->machineDealerData($dto, false));
        }

        return $dto;
    }

    public function swapDtoForReport(StatsDto $dto): StatsDto
    {

        if($dto->status){
            if(is_string($dto->status) && $dto->status !== self::ALL){
                $dto->status = [$dto->status];
            }
            if($dto->status === self::ALL){
                $dto->status = ReportStatus::list();
            }
        }

        if($dto->country){
            if(is_string($dto->country) && $dto->country !== self::ALL){
                $dto->country = [$dto->country];
            }
            if($dto->country === self::ALL){
                $dto->country = array_flip($this->reportCountryData($dto, false));
            }
        }

        if($dto->dealer){
            if(is_string($dto->dealer) && $dto->dealer !== self::ALL){
                $dto->dealer = [$dto->dealer];
            }
            if($dto->dealer === self::ALL){
                $dto->dealer = array_flip($this->reportDealerData($dto, false));
            }
        }

        if($dto->eg){
            if(is_string($dto->eg) && $dto->eg !== self::ALL){
                $dto->eg = [$dto->eg];
            }
            if($dto->eg === self::ALL){
                $dto->eg = array_flip($this->reportEgData($dto, false));
            }
        }
        if($dto->md){
            if(is_string($dto->md) && $dto->md !== self::ALL){
                $dto->md = [$dto->md];
            }
            if($dto->md === self::ALL){
                $dto->md = array_flip($this->reportMdData($dto, false));
            }
        }

        if($dto->type){
            if(is_string($dto->type) && $dto->type !== self::ALL){
                $dto->type = [$dto->type];
            }
            if($dto->type === self::ALL){
                $dto->type = array_flip($this->reportTypeData($dto, false));
            }
        }

        if($dto->size){
            if(is_string($dto->size) && $dto->size !== self::ALL){
                $dto->size = [$dto->size];
            }
            if($dto->size === self::ALL){
                $dto->size = array_flip($this->reportSizeData($dto, false));
            }
        }

        if($dto->crop){
            if(is_string($dto->crop) && $dto->crop !== self::ALL){

                $dto->crop = [$dto->crop];
            }
            if($dto->crop === self::ALL){
                $dto->crop = array_flip($this->reportCropData($dto, false));
            }
        }

        return $dto;
    }

    protected function prettyCountry(string $country): string
    {
        return trim(last(explode('-', $country)));
    }

    protected function finalView(array $data): array
    {
        $tmp = [];
        foreach ($data ?? [] as $id => $item){
            $tmp[$id] = $item['name'] . ' ('. $item['count'] .')';
        }

        return $tmp;
    }

    public function reportStatusData(StatsDto $dto): array
    {
        $statuses = ReportStatus::listWithName();

        $data = \DB::table('reports')
            ->selectRaw('COUNT(*) as count , status')
            ->whereYear('reports.created_at', $dto->year)
            ->groupByRaw('reports.status')
            ->get()->pluck('count', 'status')
        ;

        foreach ($statuses as $key => $item){
            $count = 0;
            if(isset($data[$key])){
                $count = $data[$key];
            }
            $statuses[$key] = "{$item} ({$count})";
        }

        return $statuses;
    }

    public function reportCountryData(StatsDto $dto, $swapDto = true): array
    {
        $dto = $swapDto ? $this->swapDtoForReport($dto) : $dto;

        $temp = [];
        $data = \DB::table('reports')
            ->join('users', 'users.id', '=', 'reports.user_id')
            ->join('jd_dealers', 'users.dealer_id', '=', 'jd_dealers.id')
            ->selectRaw('jd_dealers.country')
            ->selectRaw('COUNT(*) as count_reports')
            ->whereYear('reports.created_at', $dto->year)
            ->whereIn('reports.status', $dto->status)
            ->groupByRaw('jd_dealers.country')
            ->get();

        foreach ($data as $item){
            $temp[$item->country] = $this->prettyCountry($item->country) . ' (' . $item->count_reports . ')';
        }

        return $temp;
    }

    public function reportDealerData(StatsDto $dto, $swapDto = true): array
    {
        $dto = $swapDto ? $this->swapDtoForReport($dto) : $dto;

        $data = \DB::table('reports')
            ->join('users', 'users.id', '=', 'reports.user_id')
            ->join('jd_dealers', 'users.dealer_id', '=', 'jd_dealers.id')
            ->selectRaw('jd_dealers.country')
            ->selectRaw('COUNT(*) as count_reports')
            ->selectRaw('GROUP_CONCAT(DISTINCT jd_dealers.id) as dealers')
            ->whereYear('reports.created_at', $dto->year)
            ->whereIn('reports.status', $dto->status)
            ->whereIn('jd_dealers.country', $dto->country)
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
            ->whereYear('reports.created_at', $dto->year)
            ->whereIn('reports.status', $dto->status)
            ->whereIn('jd_dealers.id', $dealerIds)
            ->groupByRaw('jd_dealers.id')
            ->get();

        $dealers = [];
        foreach ($counts as $item){
            $dealers[$item->id] = $item->name . ' (' . $item->count . ')';
        }

        return $dealers;
    }

    public function reportEgData(StatsDto $dto, $swapDto = true): array
    {
        $dto = $swapDto ? $this->swapDtoForReport($dto) : $dto;

        $reports = Report::query()
            ->with([
                'user.dealer',
                'reportMachines.equipmentGroup',
                'reportMachines.modelDescription'
            ])
            ->onlyCombine()
            ->whereYear('created_at', $dto->year)
            ->whereIn('status', $dto->status)
            ->whereHas('user', function($q) use ($dto){
                $q->whereIn('dealer_id', $dto->dealer);
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

    public function reportMdData(StatsDto $dto, $swapDto = true): array
    {
        $dto = $swapDto ? $this->swapDtoForReport($dto) : $dto;

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
            ->get()
            ->toArray()
        ;
//dd($reports, $dto);
        $temp = [];

        foreach ($reports as $report){
            if(isset($report['report_machines'][0]['equipment_group'])){
                if(isset($report['report_machines'][0]['model_description']['id'])){
                    if(  isset($temp[$report['report_machines'][0]['model_description']['id']])){
                        $temp[$report['report_machines'][0]['model_description']['id']]['count'] += 1;
                    } else {
                        if(isset($report['report_machines'][0]['model_description']['name'])){
                            $temp[$report['report_machines'][0]['model_description']['id']]['name'] = $report['report_machines'][0]['model_description']['name'];
                            $temp[$report['report_machines'][0]['model_description']['id']]['count'] = 1;
                        }
                    }
                }

            }
        }
//dd($temp);
        return $this->finalView($temp);
    }

    public function reportTypeData(StatsDto $dto, $swapDto = true): array
    {
        $dto = $swapDto ? $this->swapDtoForReport($dto) : $dto;
//dd($dto);
        $models = Report::query()
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
                $q->whereIn('equipment_group_id', $dto->eg)
                    ->whereHas('modelDescription.product', function ($q) {
                        $q->whereNotNull('type');
                    })
                    ->whereIn('model_description_id', $dto->md)
                ;
            })
            ->get()
            ->toArray()
        ;
//dd($models);
        $tmp = [];
        foreach ($models as $model){
            if(isset($model['report_machines'][0]['equipment_group'])){
                if($model['report_machines'][0]['model_description']['product']['type']){
                    if(isset($tmp[$model['report_machines'][0]['model_description']['product']['type']])){
                        $tmp[$model['report_machines'][0]['model_description']['product']['type']]['count'] += 1;
                    } else {
                        $tmp[$model['report_machines'][0]['model_description']['product']['type']]['name'] = $model['report_machines'][0]['model_description']['product']['type'];
                        $tmp[$model['report_machines'][0]['model_description']['product']['type']]['count'] = 1;
                    }
                }
            }
        }
//dd($tmp);dd($tmp);
        return $this->finalView($tmp);
    }

    public function reportSizeData(StatsDto $dto, $swapDto = true): array
    {
        $dto = $swapDto ? $this->swapDtoForReport($dto) : $dto;

        $models = Report::query()
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
                $q->whereIn('equipment_group_id', $dto->eg)
                    ->whereHas('modelDescription.product', function ($q) {
                        $q->whereNotNull('size_name');
                    })
                    ->whereIn('model_description_id', $dto->md);
                ;
            })
            ->get()
            ->toArray()
        ;

        $tmp = [];
        foreach ($models as $model){
            if(isset($model['report_machines'][0]['equipment_group'])){
                if($model['report_machines'][0]['model_description']['product']['id']){
                    if(isset($tmp[$model['report_machines'][0]['model_description']['product']['id']])){
                        $tmp[$model['report_machines'][0]['model_description']['product']['id']]['count'] += 1;
                    } else {
                        $tmp[$model['report_machines'][0]['model_description']['product']['id']]['name'] = $model['report_machines'][0]['model_description']['product']['size_name'];
                        $tmp[$model['report_machines'][0]['model_description']['product']['id']]['count'] = 1;
                    }
                }
            }
        }

        return $this->finalView($tmp);
    }

    public function reportCropData(StatsDto $dto, $swapDto = true): array
    {
        $dto = $swapDto ? $this->swapDtoForReport($dto) : $dto;
//dd($dto);
        $models = Report::query()
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
                $q->whereIn('equipment_group_id', $dto->eg)
                    ->whereIn('model_description_id', $dto->md);
            })
            ->whereHas('features.value', function($q) use ($dto){
                $q->where('feature_id', $dto->feature);
            })
            ->get()
            ->toArray()
        ;

        $tmp = [];

        foreach ($models as $k => $model){
            $targetFeature = null;

            foreach ($model['features'] as $item) {
                if ($item['feature_id'] == $dto->feature) {
                    $targetFeature = $item;
                }
            }

            if (isset($targetFeature['value']['value'])) {
                if (isset($tmp[$targetFeature['value']['value_id']])) {
                    $tmp[$targetFeature['value']['value_id']]['count'] += 1;
                } else {
                    $tmp[$targetFeature['value']['value_id']]['name'] = $targetFeature['value']['value_current']['name'] ?? null;
                    $tmp[$targetFeature['value']['value_id']]['count'] = 1;
                }
            }
        }

        return $this->finalView($tmp);
    }
}
