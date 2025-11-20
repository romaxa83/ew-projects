<?php

namespace App\Console\Commands\Statistics;

use App\Models\JD\Dealer;
use App\Models\JD\EquipmentGroup;
use App\Models\JD\ModelDescription;
use App\Models\Report\Location;
use App\Repositories\JD\DealersRepository;
use App\Repositories\JD\EquipmentGroupRepository;
use App\Repositories\Report\LocationRepository;
use App\Repositories\Report\ReportRepository;
use App\Services\StatisticService;
use App\Type\ReportStatus;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class TestMachines extends Command
{

    protected $signature = 'jd:stats:machines';

    protected $description = 'Статистика по техники';
    /**
     * @var LocationRepository
     */
    private $reportLocationRepository;
    /**
     * @var DealersRepository
     */
    private $dealersRepository;
    /**
     * @var EquipmentGroupRepository
     */
    private $equipmentGroupRepository;
    /**
     * @var ReportRepository
     */
    private $reportRepository;
    /**
     * @var StatisticService
     */
    private $statisticService;


    public function __construct(
        LocationRepository $reportLocationRepository,
        DealersRepository $dealersRepository,
        EquipmentGroupRepository $equipmentGroupRepository,
        ReportRepository $reportRepository,
        StatisticService $statisticService
    )
    {
        parent::__construct();
        $this->reportLocationRepository = $reportLocationRepository;
        $this->dealersRepository = $dealersRepository;
        $this->equipmentGroupRepository = $equipmentGroupRepository;
        $this->reportRepository = $reportRepository;
        $this->statisticService = $statisticService;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $choiceYear = $this->choice(
            'Choice year',
            [2020, 2021]
        );
        $country = $this->choiceCountry($choiceYear);
        $dealerId = $this->choiceDealer($choiceYear, $country);
        $egId = $this->choiceEg($choiceYear, $country, $dealerId);
        $mdId = $this->choiceMd($choiceYear, $country, $dealerId, $egId);


//        $dealerId = 32;
//        $egId = 38;
//        $mdId = 1448;
//        $country = "Україна";
//        $choiceYear = "2021";

//        dd(implode(',', $dealerId),
//            implode(',', $egId),
//            implode(',', $mdId),
//            implode(',', $country),
//            $choiceYear);

        $reports = $this->reportRepository->getForStatistic(
            implode(',', $dealerId),
            implode(',', $egId),
            implode(',', $mdId),
            implode(',', $country),
            $choiceYear
        );

        $data = $this->statisticService->statisticMachine($reports);
        dd($data);
    }

    private function choiceCountry($year): array
    {
        $countries = $this->reportLocationRepository
            ->getListByFilter(Location::TYPE_COUNTRY_FILTER, null);
        $countries = array_reverse($countries);

        foreach ($countries ?? [] as $item){
            $count = $this->reportLocationRepository
                ->countReportByCountryAndYear($item, $year, [ReportStatus::CREATED, ReportStatus::EDITED]);
            $countries[$item] = $item . " ({$count})";
        }

        $temp = array_values($countries);
        $choiceTemps = $this->choice(
            'Choice country',
            $temp,
            null,
            null,
            true
        );

        foreach ($choiceTemps ?? [] as $item){
            $choiceCountry[] = array_flip($countries)[$item];
        }

        return $choiceCountry;
    }

    private function choiceDealer($year, array $country): array
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
                    ->whereIn('status' ,[ReportStatus::CREATED, ReportStatus::EDITED])
                    ->whereYear('created_at', $year)
                    ->whereHas('location', function (Builder $q) use($country) {
//                        $q->where('country', $country);
                        $q->whereIn('country', $country);
                    })
                    ->count();

                $count += $c;
            }

            $temp[$dealer->id] = $dealer->name . " ({$count})";
        }

        $choiceTemps = $this->choice(
            'Choice dealer',
            array_values($temp),
            null,
            null,
            true
        );

        foreach ($choiceTemps ?? [] as $item){
            $choiceDealer[] = array_flip($temp)[$item];
        }

        return $choiceDealer;
    }

    private function choiceEg($year, array $country, array $dealerId): array
    {
        $egs = EquipmentGroup::query()
            ->withCount(['reportMachines'])
            ->where('for_statistic', true)
            ->get()
        ;

        $temp = [];
        foreach ($egs as $eg){
            $c = $eg->reportMachines()
                ->with('reports')
                ->whereHas('reports', function($q) use($year, $country, $dealerId) {
                    $q->whereIn('status' ,[ReportStatus::CREATED, ReportStatus::EDITED])
                        ->whereYear('created_at', $year)
                        ->whereHas('location', function (Builder $q) use($country) {
//                            $q->where('country', $country);
                            $q->whereIn('country', $country);
                        })->whereHas('user', function($q) use($dealerId) {
//                            $q->where('dealer_id', $dealerId);
                            $q->whereIn('dealer_id', $dealerId);
                        });
                    })
                ->count();

            $temp[$eg->id] = $eg->name . " ({$c})";
        }

        $choiceTemps = $this->choice(
            'Choice Eg',
            array_values($temp),
            null,
            null,
            true
        );

        foreach ($choiceTemps ?? [] as $item){
            $choiceEg[] = array_flip($temp)[$item];
        }

        return $choiceEg;
    }

    private function choiceMd($year, array $country, array $dealerId, array $egId)
    {
        $mds = ModelDescription::query()
            ->with(['reportMachine'])
            ->withCount(['reportMachine'])
            ->whereHas('reportMachine', function (Builder $q) use($egId) {
                $q->whereIn('equipment_group_id', $egId);
            })->get();

        $temp = [];
        foreach ($mds as $md){
            $c = $md->reportMachine()
                ->with('reports')
                ->whereHas('reports', function($q) use($year, $country, $dealerId) {
                    $q->whereIn('status' ,[ReportStatus::CREATED, ReportStatus::EDITED])
                        ->whereYear('created_at', $year)
                        ->whereHas('location', function (Builder $q) use($country) {
//                            $q->where('country', $country);
                            $q->whereIn('country', $country);
                        })->whereHas('user', function($q) use($dealerId) {
//                            $q->where('dealer_id', $dealerId);
                            $q->whereIn('dealer_id', $dealerId);
                        });
                })
                ->count();

            $temp[$md->id] = $md->name . " ({$c})";
        }

        $choiceTemps = $this->choice(
            'Choice Md',
            array_values($temp),
            null,
            null,
            true
        );

        foreach ($choiceTemps ?? [] as $item){
            $choiceMd[] = array_flip($temp)[$item];
        }

        return $choiceMd;
    }
}
