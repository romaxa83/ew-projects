<?php

namespace Core\Sms;

use BadMethodCallException;
use Core\Contracts\Sms\SmsTransportContract;
use Core\Sms\Transport\ArrayTransport;
use Core\Sms\Transport\ClickSendTransport;
use Core\Sms\Transport\Transport;
use Illuminate\Contracts\Queue\Factory as QueueContract;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * @mixin SmsSender
 */
class SmsManager implements SmsFactory
{
    protected array $smsSenders = [];
    protected QueueContract $queue;

    public function __call($method, $parameters)
    {
        return $this->smsTransport()->$method(...$parameters);
    }

    public function smsTransport(string $name = null): SmsTransportContract
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->smsSenders[$name] = $this->get($name);
    }

    public function getDefaultDriver(): string
    {
        return config('sms.default');
    }

    protected function get($name): SmsSender
    {
        return $this->smsSenders[$name] ?? $this->resolve($name);
    }

    protected function resolve(string $name): SmsSender
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Sms driver [$name] is not defined.");
        }

        $sender = new SmsSender();

        $sender->setTransport(
            $this->getTransport($name)
        );

        if (app()->bound('queue')) {
            $sender->setQueue(app('queue'));
        }

        return $sender;
    }

    protected function getConfig(string $name): ?array
    {
        return config("sms.drivers.$name");
    }

    protected function getTransport(string $name): Transport
    {
        $method = 'get' . Str::studly($name) . 'Transport';

        if (!method_exists($this, $method)) {
            throw new BadMethodCallException("Method $method does not exists");
        }

        return $this->$method();
    }

    protected function getClicksendTransport(): ClickSendTransport
    {
        $transport = new ClickSendTransport();

        $transport->setConfig(
            config('services.clicksend')
        );

        return $transport;
    }

    protected function getArrayTransport(): ArrayTransport
    {
        return new ArrayTransport();
    }
}
