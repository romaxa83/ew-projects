<?php

namespace App\Console\Commands\Syncs\BS;

use App\Console\Commands\BaseCommand;
use App\Foundations\Helpers\DbConnections;
use App\Models\Inventories\Inventory;
use App\Models\TypeOfWorks\TypeOfWork;
use App\Models\TypeOfWorks\TypeOfWorkInventory;
use Symfony\Component\Console\Helper\ProgressBar;

class FetchTypeOfWork extends BaseCommand
{
    protected $signature = 'sync:bs_type_of_work';

    public function exec(): void
    {
        echo "[x] START... fetch type of work" . PHP_EOL;

        $inventories = Inventory::query()->get()->pluck('id','origin_id')->toArray();

        try {
            $data = DbConnections::haulk()
                ->table('bs_types_of_work')
                ->get()
                ->toArray()
            ;

            $relations = DbConnections::haulk()
                ->table('bs_type_of_work_inventories')
                ->get()
                ->toArray()
            ;

            $progressBar = new ProgressBar($this->output, count($data));
            $progressBar->setFormat('verbose');
            $progressBar->start();

            foreach ($data as $k => $item){
                if(!TypeOfWork::query()->where('id', $item->id)->exists()){
                    $c = new TypeOfWork();
                    $c->id = $item->id;
                    $c->name = $item->name;
                    $c->duration = $item->duration;
                    $c->hourly_rate = $item->hourly_rate;
                    $c->created_at = $item->created_at;
                    $c->updated_at = $item->updated_at;
                    $c->save();

                    foreach ($relations as $relation){
                        if($relation->type_of_work_id == $item->id){
                            $i = new TypeOfWorkInventory();
                            $i->quantity = $relation->quantity;
                            $i->created_at = $relation->created_at;
                            $i->updated_at = $relation->updated_at;
                            $i->type_of_work_id = $c->id;
                            $i->inventory_id = $inventories[$relation->inventory_id];
                            $i->save();
                        }
                    }

                    $progressBar->advance();
                }
            }

            $progressBar->finish();
            echo PHP_EOL;
            echo "[x]  DONE fetch fetch type of work" . PHP_EOL;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
    }
}
