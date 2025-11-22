<?php

namespace App\Console\Commands\Syncs\BS;

use App\Console\Commands\BaseCommand;
use App\Foundations\Helpers\DbConnections;
use App\Models\Vehicles\Make;
use App\Models\Vehicles\Model;
use Carbon\CarbonImmutable;
use Symfony\Component\Console\Helper\ProgressBar;

class FetchMakes extends BaseCommand
{
    protected $signature = 'sync:bs_vehicle_makes';

    public function exec(): void
    {
        echo "[x] START... fetch vehicle makes" . PHP_EOL;

        $chunk = 200;
        $count = DbConnections::haulk()->table('vehicle_makes')->count();
        $progressBar = new ProgressBar($this->output, $count);
        $progressBar->setFormat('verbose');
        $progressBar->start();

        DbConnections::haulk()
            ->table('vehicle_makes')
            ->join(
                'vehicle_models',
                'vehicle_makes.id',
                '=',
                'vehicle_models.make_id'
            )
            ->select('vehicle_makes.id', 'vehicle_makes.name', 'vehicle_makes.last_updated',
                \DB::raw('array_agg(vehicle_models.name || \'%\' ||vehicle_models.id) as models')
            )
            ->groupBy('vehicle_makes.id')
            ->orderBy('vehicle_makes.id')
//            ->limit(10)
            ->chunk($chunk, function($data) use ($chunk, $progressBar) {

                $progressBar->advance($chunk);
                $tmp = [];
                $models = [];
                foreach ($data as $item){
                    $str = $item->models;
                    $trimmed = trim($str, '{}');
                    $array = str_getcsv($trimmed);
                    foreach ($array as $strData){
                        $d = explode('%', $strData);
                        $models[] = [
                            'id' => (int)last($d),
                            'name' => current($d),
                            'make_id' => $item->id,
                        ];
                    }

                    $tmp[] = [
                        'id' => $item->id,
                        'name' => $item->name,
                        'last_updated' => CarbonImmutable::createFromTimestamp($item->last_updated),
                    ];
                }


                \DB::table(Make::TABLE)->upsert($tmp, ['id']);
                \DB::table(Model::TABLE)->upsert($models, ['id']);
            })
        ;

        $progressBar->finish();
        echo PHP_EOL;
        echo "[x]  DONE fetch vehicle makes" . PHP_EOL;
    }
}
