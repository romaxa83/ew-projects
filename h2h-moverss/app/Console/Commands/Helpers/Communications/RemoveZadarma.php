<?php

namespace App\Console\Commands\Helpers\Communications;

use App\Models\Communications\CommunicationRecord;
use App\Models\Zadarma\CallsEvents;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\Console\Helper\ProgressBar;

class RemoveZadarma extends Command
{
    protected $signature = 'helpers:remove-zadarma';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $start = microtime(true);

            $this->exec();

            $time = microtime(true) - $start;

            echo PHP_EOL;
            $this->info("Done [time = {$time}]");
            echo PHP_EOL;

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    public function exec()
    {
        $this->zadarma();
    }

    public function zadarma()
    {
        $count = 0;
        $chunk = 300;

        $query = CommunicationRecord::query()
            ->where('entity_type', CallsEvents::MORPH_NAME)
            ->whereHasMorph('entity', CallsEvents::class, function ($q){
                $q->whereNotIn('event', [
                    CallsEvents::EVENT_NOTIFY_OUT_END,
                    CallsEvents::EVENT_NOTIFY_END
                ]);
            })
        ;

        $this->info('ZADARMA RECS');
        $progressBar = new ProgressBar($this->output, $query->count());
        $progressBar->setFormat('verbose');
        $progressBar->start();
        try {
            $query->chunk($chunk, function (Collection $items) use ($progressBar, $chunk, &$count) {
                $progressBar->advance($chunk);
                $items->each(function (CommunicationRecord $item) use ($progressBar, $chunk, &$count) {



                    if(
                        !($item->entity->event == CallsEvents::EVENT_NOTIFY_END
                         || $item->entity->event == CallsEvents::EVENT_NOTIFY_OUT_END)
                    ){
                        $item->delete();
                    }
                });
            });

        } catch (\Throwable $e) {
            dd($e->getMessage());
        }

        $progressBar->finish();
        echo PHP_EOL;
        $this->info("ZADARMA RECS remove [{$count}]");
        echo PHP_EOL;
    }

}


