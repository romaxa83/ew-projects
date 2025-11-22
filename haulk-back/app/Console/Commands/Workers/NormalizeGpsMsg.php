<?php

namespace App\Console\Commands\Workers;

use App\Models\GPS\History;
use App\Services\Saas\GPS\Histories\HistoryService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class NormalizeGpsMsg extends Command
{
    protected $signature = 'worker:normalize_gps_msg';

    protected $count = 0;

    public function __construct(
        HistoryService $service
    )
    {
        parent::__construct();

        $this->service = $service;
    }

    public function handle()
    {
        try {
            $start = microtime(true);

            $date = CarbonImmutable::now();

            $this->exec($date);

            $time = microtime(true) - $start;

            logger_info("[worker] ".__CLASS__." [time = {$time}], [$this->count]");
            $this->info("[worker] ".__CLASS__." [time = {$time}], [$this->count]");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            logger_info("[worker] ".__CLASS__."  FAIL", [
                'msg' => $e->getMessage()
            ]);
            return self::FAILURE;
        }
    }

    private function exec(CarbonImmutable $date)
    {

        $this->assertLongIdle($date);

        $this->removeDuplicateFromTruck($date);
        $this->removeDuplicateFromTrailer($date);
    }

    public function assertLongIdle($date)
    {
        $result = History::query()
            ->whereDate('created_at', $date)
            ->where('event_type', History::EVENT_LONG_IDLE)
            ->where('event_duration', '<', config('gps.long_idle_min_duration'))
            ->update(['event_type' => History::EVENT_IDLE]);


        logger_info("[worker] ".__CLASS__." [". __FUNCTION__ ." = {$result}]");
        $this->info("[worker] ".__CLASS__." [". __FUNCTION__ ." = {$result}]");
    }

    public function removeDuplicateFromTruck(CarbonImmutable $date)
    {
        $truckIds = [];

        History::query()
            ->select('truck_id')
            ->whereDate('created_at', $date)
            ->groupBy('truck_id')
            ->toBase()
            ->get()
            ->map(function($i) use (&$truckIds) {
                $truckIds[] = $i->truck_id;
            })
        ;

        foreach ($truckIds ?? [] as $id){
            $records = History::query()
                ->select(['id', 'event_type'])
                ->whereDate('created_at', $date)
                ->where('truck_id', $id)
                ->orderBy('received_at', 'asc')
                ->toBase()
                ->get();

            $previousValue = null;
            $consecutiveRecords = [];
            $results = [];

            // сортируем записи по последовательности события
            foreach ($records as $record) {
                if ($record->event_type == $previousValue) {
                    // Если значение совпадает с предыдущим, добавляем запись в массив
                    $consecutiveRecords[] = $record;
                } else {
                    // Если значения не совпадают и массив уже содержит данные, сохраняем его и начинаем новую группу
                    if (count($consecutiveRecords) > 1) {
                        $results[] = $consecutiveRecords;
                    }
                    $consecutiveRecords = [$record];
                }
                $previousValue = $record->event_type;
            }

            // Проверяем последнюю группу после выхода из цикла
            if (count($consecutiveRecords) > 1) {
                $results[] = $consecutiveRecords;
            }

            // фильтруем данные, нам нужно события не драйвинг
            $tmp = [];
            foreach ($results as $res) {
                if(
                    count($res) > 1
                    && $res[0]->event_type !== History::EVENT_DRIVING
                ) {
                    $tmp[] = $res;
                }
            }

            if(!empty($tmp)){
                $count = count($tmp);
                logger_info("[worker] ".__CLASS__." [". __FUNCTION__ ." = {$count}]");
                $this->info("[worker] ".__CLASS__." [". __FUNCTION__ ." = {$count}]");
            }

            // у задвоенных событий оставляем только одну запись
            foreach ($tmp as $data){
                $ids = [];
                foreach ($data as $i){
                    $ids[] = $i->id;
                }
                $models = History::query()
                    ->whereIn('id', $ids)
                    ->toBase()
                    ->get()
                ;

                $max = $models->max('msg_count_for_duration');
                $model = $models->firstWhere('msg_count_for_duration', $max);

                $valueToRemove = $model->id;

                if (($key = array_search($valueToRemove, $ids)) !== false) {
                    unset($ids[$key]);
                }

                History::query()
                    ->whereIn('id', $ids)
                    ->delete()
                ;
            }
        }
    }

    public function removeDuplicateFromTrailer(CarbonImmutable $date)
    {
        $trailerIds = [];

        History::query()
            ->select('trailer_id')
            ->whereDate('created_at', $date)
            ->groupBy('trailer_id')
            ->toBase()
            ->get()
            ->map(function($i) use (&$trailerIds) {
                $trailerIds[] = $i->trailer_id;
            })
        ;

        foreach ($trailerIds ?? [] as $id){
            $records = History::query()
                ->select(['id', 'event_type'])
                ->whereDate('created_at', $date)
                ->where('trailer_id', $id)
                ->orderBy('received_at', 'asc')
                ->toBase()
                ->get();

            $previousValue = null;
            $consecutiveRecords = [];
            $results = [];

            // сортируем записи по последовательности события
            foreach ($records as $record) {
                if ($record->event_type == $previousValue) {
                    // Если значение совпадает с предыдущим, добавляем запись в массив
                    $consecutiveRecords[] = $record;
                } else {
                    // Если значения не совпадают и массив уже содержит данные, сохраняем его и начинаем новую группу
                    if (count($consecutiveRecords) > 1) {
                        $results[] = $consecutiveRecords;
                    }
                    $consecutiveRecords = [$record];
                }
                $previousValue = $record->event_type;
            }

            // Проверяем последнюю группу после выхода из цикла
            if (count($consecutiveRecords) > 1) {
                $results[] = $consecutiveRecords;
            }

            // фильтруем данные, нам нужно события не драйвинг
            $tmp = [];
            foreach ($results as $res) {
                if(
                    count($res) > 1
                    && $res[0]->event_type !== History::EVENT_DRIVING
                ) {
                    $tmp[] = $res;
                }
            }

            if(!empty($tmp)){
                $count = count($tmp);
                logger_info("[worker] ".__CLASS__." [". __FUNCTION__ ." = {$count}]");
                $this->info("[worker] ".__CLASS__." [". __FUNCTION__ ." = {$count}]");
            }

            // у задвоенных событий оставляем только одну запись
            foreach ($tmp as $data){
                $ids = [];
                foreach ($data as $i){
                    $ids[] = $i->id;
                }
                $models = History::query()
                    ->whereIn('id', $ids)
                    ->toBase()
                    ->get()
                ;

                $max = $models->max('msg_count_for_duration');
                $model = $models->firstWhere('msg_count_for_duration', $max);

                $valueToRemove = $model->id;

                if (($key = array_search($valueToRemove, $ids)) !== false) {
                    unset($ids[$key]);
                }

                History::query()
                    ->whereIn('id', $ids)
                    ->delete()
                ;
            }
        }
    }
}
