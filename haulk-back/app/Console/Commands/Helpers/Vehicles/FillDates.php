<?php

namespace App\Console\Commands\Helpers\Vehicles;

use App\Enums\Format\DateTimeEnum;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Services\Orders\OrderService;
use Illuminate\Console\Command;

class FillDates extends Command
{
    protected $signature = 'helper:vehicles_fill_dates';

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

            $this->info("Done [time = {$time}]");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    private function exec(): void
    {
       $this->trucks();
       $this->trailers();
    }

    private function trucks()
    {
        $counter = 0;

        Truck::query()
            ->each(function (Truck $model) use (&$counter) {
                $tmp = [];
                if($model->registration_date && is_null($model->registration_date_as_str)) {
                    $tmp['registration_date_as_str'] = $model->registration_date->format(DateTimeEnum::DATE_FRONT);
                }
                if($model->registration_expiration_date && is_null($model->registration_expiration_date_as_str)) {
                    $tmp['registration_expiration_date_as_str'] = $model->registration_expiration_date->format(DateTimeEnum::DATE_FRONT);
                }
                if($model->inspection_date && is_null($model->inspection_date_as_str)) {
                    $tmp['inspection_date_as_str'] = $model->inspection_date->format(DateTimeEnum::DATE_FRONT);
                }
                if($model->inspection_expiration_date && is_null($model->inspection_expiration_date_as_str)) {
                    $tmp['inspection_expiration_date_as_str'] = $model->inspection_expiration_date->format(DateTimeEnum::DATE_FRONT);
                }

                if(!empty($tmp)) {
                    $model->update($tmp);
                    $counter++;
                }
            });

        echo PHP_EOL . "[x] Updated {$counter} trucks." . PHP_EOL;
    }

    private function trailers()
    {
        $counter = 0;

        Trailer::query()
            ->each(function (Trailer $model) use (&$counter) {
                $tmp = [];
                if($model->registration_date && is_null($model->registration_date_as_str)) {
                    $tmp['registration_date_as_str'] = $model->registration_date->format(DateTimeEnum::DATE_FRONT);
                }
                if($model->registration_expiration_date && is_null($model->registration_expiration_date_as_str)) {
                    $tmp['registration_expiration_date_as_str'] = $model->registration_expiration_date->format(DateTimeEnum::DATE_FRONT);
                }
                if($model->inspection_date && is_null($model->inspection_date_as_str)) {
                    $tmp['inspection_date_as_str'] = $model->inspection_date->format(DateTimeEnum::DATE_FRONT);
                }
                if($model->inspection_expiration_date && is_null($model->inspection_expiration_date_as_str)) {
                    $tmp['inspection_expiration_date_as_str'] = $model->inspection_expiration_date->format(DateTimeEnum::DATE_FRONT);
                }

                if(!empty($tmp)) {
                    $model->update($tmp);
                    $counter++;
                }
            });

        echo PHP_EOL . "[x] Updated {$counter} trailers." . PHP_EOL;
    }
}
