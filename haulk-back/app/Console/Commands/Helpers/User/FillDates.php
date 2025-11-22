<?php

namespace App\Console\Commands\Helpers\User;

use App\Enums\Format\DateTimeEnum;
use App\Models\Users\DriverInfo;
use App\Models\Users\DriverLicense;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Services\Orders\OrderService;
use Illuminate\Console\Command;

class FillDates extends Command
{
    protected $signature = 'helper:drivers_fill_dates';

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
       $this->licenses();
       $this->medical();
    }

    private function licenses()
    {
        $counter = 0;

        DriverLicense::query()
            ->each(function (DriverLicense $model) use (&$counter) {
                $tmp = [];
                if($model->issuing_date && is_null($model->issuing_date_as_str)) {
                    $tmp['issuing_date_as_str'] = $model->issuing_date->format(DateTimeEnum::DATE_FRONT);
                }
                if($model->expiration_date && is_null($model->expiration_date_as_str)) {
                    $tmp['expiration_date_as_str'] = $model->expiration_date->format(DateTimeEnum::DATE_FRONT);
                }

                if(!empty($tmp)) {
                    $model->update($tmp);
                    $counter++;
                }
            });

        echo PHP_EOL . "[x] Updated {$counter} licenses." . PHP_EOL;
    }

    private function medical()
    {
        $counter = 0;

        DriverInfo::query()
            ->each(function (DriverInfo $model) use (&$counter) {
                $tmp = [];
                if($model->medical_card_issuing_date && is_null($model->medical_card_issuing_date_as_str)) {
                    $tmp['medical_card_issuing_date_as_str'] = $model->medical_card_issuing_date->format(DateTimeEnum::DATE_FRONT);
                }
                if($model->medical_card_expiration_date && is_null($model->medical_card_expiration_date_as_str)) {
                    $tmp['medical_card_expiration_date_as_str'] = $model->medical_card_expiration_date->format(DateTimeEnum::DATE_FRONT);
                }
                if($model->mvr_reported_date && is_null($model->mvr_reported_date_as_str)) {
                    $tmp['mvr_reported_date_as_str'] = $model->mvr_reported_date->format(DateTimeEnum::DATE_FRONT);
                }

                if(!empty($tmp)) {
                    $model->update($tmp);
                    $counter++;
                }
            });

        echo PHP_EOL . "[x] Updated {$counter} medical." . PHP_EOL;
    }
}
