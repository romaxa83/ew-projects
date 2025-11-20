<?php

namespace App\Console\Commands;

use App\Models\JD\EquipmentGroup;
use App\Repositories\JD\EquipmentGroupRepository;
use Illuminate\Console\Command;

class Statistic extends Command
{
    protected $signature = 'jd:statistic-init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Init data for statistic';
    /**
     * @var EquipmentGroupRepository
     */
    private $equipmentGroupRepository;

    public function __construct(EquipmentGroupRepository $equipmentGroupRepository)
    {
        parent::__construct();
        $this->equipmentGroupRepository = $equipmentGroupRepository;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $this->resetEg();
        $this->choiceForStatistic();
        $this->info("Done");
    }

    private function resetEg()
    {
        $count = 0;
        foreach ($this->equipmentGroupRepository->getByForStatistic() as $eq){
            /** @var $eq EquipmentGroup */
            $eq->for_statistic = false;
            $eq->save();
            $count++;
        }

        $this->info("Зброшено \"{$count}\" eg");
    }

    private function choiceForStatistic()
    {
        $count = 0;
        foreach ($this->equipmentGroupRepository->getAllForName(EquipmentGroup::forStatistics()) as $eq){
            /** @var $eq EquipmentGroup */
            $eq->for_statistic = true;
            $eq->save();
            $count++;
        }

        $this->info("Определено для фильтра \"{$count}\" eg");
    }

}

