<?php

namespace App\Console\Commands\Helpers;

use App\Models\GPS\Alert;
use App\Services\GPS\GPSDataService;
use Illuminate\Console\Command;

class ClearDuplicateAlerts extends Command
{
    protected $signature = 'helper:clear_duplicate_gps_alert';

    protected GPSDataService $service;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $records = Alert::query()
            ->select('alert_type', 'history_id', \DB::raw('COUNT(*) as count'))
            ->groupBy('alert_type', 'history_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($records as $rec){

            $alerts = Alert::query()
                ->where('alert_type', $rec->alert_type)
                ->where('history_id', $rec->history_id)
                ->get();

            foreach ($alerts as $k => $alert){
                if($k !== 0){
                    $alert->delete();
                }
            }
        }

        $this->info('Done');
        return self::SUCCESS;
    }
}
