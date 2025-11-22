<?php

namespace App\Console\Commands\GPS;

use App\Dto\GPS\DeviceGpsData;
use App\Enums\Saas\GPS\DeviceStatus;
use App\Models\GPS\Alert;
use App\Models\Saas\GPS\Device;
use App\Services\GPS\GPSDataService;
use App\Services\Saas\GPS\Flespi\FlespiClient;
use App\Services\Telegram\Telegram;
use Illuminate\Console\Command;

class PingDevices extends Command
{
    protected $signature = 'gps:ping_devices';

    protected FlespiClient $client;
    protected GPSDataService $service;

    public function __construct(
        FlespiClient $client,
        GPSDataService $GPSDataService
    )
    {
        parent::__construct();

        $this->client = $client;
        $this->service = $GPSDataService;
    }

    public function handle(): int
    {
        try {
            $start = microtime(true);

            $this->exec();

            $time = microtime(true) - $start;

        } catch (\Throwable $e) {
            Telegram::error("ALERT ". Alert::ALERT_DEVICE_CONNECTION_LOST , null, [
                'err_msd' => $e->getMessage(),
            ]);
            return self::FAILURE;
        }

        $this->info("Done [time = {$time}]");

        return self::SUCCESS;
    }

    private function exec()
    {
        Device::query()
            ->with([
                'truck.lastGPSHistory',
                'trailer.lastGPSHistory',
            ])
            ->where('status', DeviceStatus::ACTIVE())
            ->each(function (Device $device) {
                $uri = "gw/devices/{$device->flespi_device_id}/telemetry/all";

                $res = $this->client->get($uri, [], true);

                if(!empty($res)){
                    $telemetryData = $res['result'][0]['telemetry'] ?? null;

                    if($telemetryData && !empty($telemetryData)){
                        $this->service->pingDeviceConnection(
                            $device,
                            DeviceGpsData::fromTelemetry($telemetryData)
                        );
                    }
                }
            });

//        $device = Device::query()
//            ->with(['truck.lastGPSHistory'])
//            ->where('imei', '350317174414478')
//            ->first();

    }

    public function testData(): array
    {
        return [
            "result" => [
                0 => [
                    "id" => 5179239,
                    "telemetry" => [
                        "movement.status" => [
                            "ts" => 1724293878,
                            "value" => true
                        ],
                        "position" => [
                            "ts" => 1724293878,
                            "value" => [
                                "altitude" => 205,
                                "direction" => 98,
                                "hdop" => 1.6,
                                "latitude" => 46.482143,
                                "longitude" => 30.732593,
                                "pdop" => 2.7,
                                "satellites" => 8,
                                "speed" => 27,
                                "valid" => true
                            ]
                        ],
                        "position.speed" => [
                            "ts" => 1724293878,
                            "value" => 27
                        ],
                        "server.timestamp" => [
                            "ts" => 1724293884.5097,
                            "value" => 1724293884.5097
                        ],
                        "timestamp" => [
                            "ts" => 1724293878,
                            "value" => 1724293878
                        ],
                    ]
                ]
            ]
        ];
    }
}
