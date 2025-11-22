<?php

namespace App\Console\Commands\Workers;

use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT;

class FlespiMqttWorker extends Command
{
    protected $signature = 'worker:flespi_mqtt';

    public function handle()
    {
        /** @var \PhpMqtt\Client\Contracts\MqttClient $mqtt */
        $mqtt = MQTT::connection('default');

        $this->info("Start ... - {$mqtt->getClientId()}");
        $mqtt->subscribe(
            'flespi/message/gw/devices/+/telemetry',
            function (string $topic, string $message) {
                $this->info('W');
                $this->info(sprintf('Received QoS level 1 message on topic [%s]: %s', $topic, $message));
            },
//            1
        );

        $mqtt->loop(true);
    }
}
