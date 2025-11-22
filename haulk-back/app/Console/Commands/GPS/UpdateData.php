<?php

namespace App\Console\Commands\GPS;

use App\Models\GPS\Message;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;

class UpdateData extends Command
{
    protected $signature = 'gps:update-msg';

    public function handle(): int
    {
        $data = Message::all();

        $count = count($data);
        $progressBar = new ProgressBar($this->output, $count);
        $progressBar->setFormat('verbose');

        try {
            $progressBar->start();

            foreach ($data as $item){
                if($item->data){
                    /** @var $item Message */

                    $gpsData = $item->data;
                    if(!is_array($gpsData)){
                        $gpsData = json_to_array($item->data);
                        $gpsData = isset($gpsData[0]) ? $gpsData[0] : $gpsData;
                    }

                    $item->vehicle_mileage = $gpsData['vehicle.mileage'];
                    $item->speed = $gpsData['position.speed'];
                    $item->engine_off = !$gpsData['engine.ignition.status'];
                    $item->movement_status = $gpsData['movement.status'];
                    $item->position_satellites = $gpsData['position.satellites'];
                    $item->position_valid = $gpsData['position.valid'];
                    $item->server_time_at = $gpsData['server.timestamp'];
                    $item->gsm_signal_level = $gpsData['gsm.signal.level'];
                    $item->gps_fuel_rate = $gpsData['gps.fuel.rate'];
                    $item->gps_fuel_used = $gpsData['gps.fuel.used'];
                    $item->external_powersource_voltage = $gpsData['external.powersource.voltage'];
                    $item->data = $gpsData;
                    $item->save();

                    $progressBar->advance();
                }
            }

            $progressBar->finish();
            echo PHP_EOL;
            $this->info('Done');

            return self::SUCCESS;
        } catch (\Exception $e){
            $progressBar->clear();
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }
}

