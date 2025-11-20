<?php

namespace App\Console\Commands\ARI;

use App\Services\Calls\QueueService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use OpiyOrg\AriClient\Client\Rest\Settings as AriRestClientSettings;
use OpiyOrg\AriClient\Client\Rest\Resource\{
    Channels as AriChannelsRestResourceClient,
    Events as AriEventsResourceRestClient
};
use OpiyOrg\AriClient\Client\WebSocket\{
    Factory as AriWebSocketClientFactory,
    Settings as AriWebSocketClientSettings
};

class Worker extends Command
{
    protected $signature = 'workers:ari_listener';
    protected array $settings;

    public function __construct(protected QueueService $queueService)
    {
        $this->settings = config('asterisk.ari');

        parent::__construct();
    }

    public function handle()
    {
        $ariRestClientSettings = new AriRestClientSettings(
            data_get($this->settings, 'username'),
            data_get($this->settings, 'password'),
            data_get($this->settings, 'host'),
            (int)data_get($this->settings, 'port')
        );

        $ariRestClientSettings->setIsInDebugMode(data_get($this->settings, 'is_debug'));
        if(data_get($this->settings, 'logger_enable')){
            $ariRestClientSettings->setLoggerInterface(Log::channel('ari'));
        }

        $myExampleStasisApp = new StasisApp(
            new AriChannelsRestResourceClient($ariRestClientSettings)
        );

        $ariWebSocketClientSettings = new AriWebSocketClientSettings(
            $ariRestClientSettings->getUser(),
            $ariRestClientSettings->getPassword(),
            $ariRestClientSettings->getHost()
        );

        $ariWebSocketClientSettings->setErrorHandler(
            static function (string $context, \Throwable $throwable) {
                printf(
                    "\n\nThis is the error handler, triggered in context '%s'. "
                    . "Throwable message: '%s'\n\n",
                    $context,
                    $throwable->getMessage()
                );
            }
        );

        $ariWebSocketClient = AriWebSocketClientFactory::create(
            $ariWebSocketClientSettings,
            $myExampleStasisApp
        );

        $ariEventsRestClient = new AriEventsResourceRestClient($ariRestClientSettings);
        $ariChannelRestClient = new AriChannelsRestResourceClient($ariRestClientSettings);

        $queueService = $this->queueService;

        $ariWebSocketClient->getLoop()->addPeriodicTimer(
            data_get($this->settings, 'ws_interval'),
            static function () use ($ariChannelRestClient, $queueService) {
                $res = $ariChannelRestClient->list();

                $queueService->handlerChannelFromAri($res);
            }
        );

        $ariWebSocketClient->start();
    }
}
