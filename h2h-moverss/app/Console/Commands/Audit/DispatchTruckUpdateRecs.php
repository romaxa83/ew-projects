<?php

namespace App\Console\Commands\Audit;

use App\Models\Audit;
use App\Models\Order\WorkDispatchTouch;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\Console\Helper\ProgressBar;

class DispatchTruckUpdateRecs extends Command
{
    protected $signature = 'audit:dispatch_truck_update';

    public function handle()
    {
        $chunk = 100;

        $query = Audit::query()
            ->where('auditable_type','App\Models\DispatchTruck')
            ->whereNull('order_id');

        $progressBar = new ProgressBar($this->output, $query->count());
        $progressBar->setFormat('verbose');
        $progressBar->start();
        try {
            $query->chunk($chunk, function (Collection $items) use ($progressBar, $chunk) {
                $progressBar->advance($chunk);
                $items->each(function (Audit $item) use ($progressBar, $chunk) {
                    if($item->new_values && isset($item->new_values['work_id'])){
                        $w = WorkDispatchTouch::query()
                            ->select(['order_id', 'start_date'])
                            ->where('id',$item->new_values['work_id'])
                            ->first();

                        $item->update([
                            'dispatch_truck_at' => $w->start_date ?? null,
                            'order_id' => $w->order_id ?? null,
                        ]);
                    }
                });
            });

        } catch (\Throwable $e) {
//            $progressBar->clear();
            dd($e->getMessage());
        }

        $progressBar->finish();
        echo PHP_EOL;
    }
}

