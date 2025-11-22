<?php

namespace App\Console\Commands\Helpers;

use App\Models\GPS\Alert;
use App\Models\GPS\History;
use App\Services\GPS\GPSDataService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class CreateGpsAlerts extends Command
{
    protected $signature = 'helper:create_gps_alert';

    protected GPSDataService $service;

    public function __construct(
        GPSDataService $service
    )
    {
        parent::__construct();

        $this->service = $service;
    }

    public function handle()
    {
        $companyId = $this->ask('Enter Company id');

        /** @var $history History */
        $history = History::query()
            ->where('company_id', $companyId)
            ->latest()
            ->first();

        if(!$history){
            $this->error('not history');
            return self::FAILURE;
        }

        $data = [
                'history_id' => $history->id,
                'received_at' => CarbonImmutable::now(),
                'truck_id' => $history->truck_id,
                'trailer_id' => $history->trailer_id,
                'driver_id' => $history->driver_id,
                'latitude' => $history->latitude,
                'longitude' => $history->longitude,
                'alert_type' => Alert::ALERT_TYPE_SPEEDING,
                'company_id' => $companyId,
        ];

        $this->service->createAlert($data);

        $this->info('Done');
        return self::SUCCESS;
    }
}
