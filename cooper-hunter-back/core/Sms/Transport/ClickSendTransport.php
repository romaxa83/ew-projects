<?php

namespace Core\Sms\Transport;

use ClickSend\Api\SMSApi;
use ClickSend\Configuration;
use ClickSend\Model\SmsMessage;
use ClickSend\Model\SmsMessageCollection;
use Core\Sms\SendQueuedSms;
use GuzzleHttp\Client as GuzzleClient;
use Throwable;

class ClickSendTransport extends Transport
{
    protected Configuration $configuration;

    protected SMSApi $smsClient;

    protected GuzzleClient $guzzleClient;

    protected array $config;

    public function send(\Core\Sms\SmsMessage $message): void
    {
        try {
            $response = $this->smsClient()->smsSendPost(
                $this->smsMessageCollection($message)
            );

            info($response);
        } catch (Throwable $e) {
            logger($e);
        }
    }

    protected function smsClient(): SMSApi
    {
        if (empty($this->smsClient)) {
            $this->smsClient = new SMSApi(
                $this->guzzle(),
                $this->getConfiguration(),
            );
        }

        return $this->smsClient;
    }

    protected function guzzle(): GuzzleClient
    {
        if (empty($this->guzzleClient)) {
            $this->guzzleClient = new GuzzleClient();
        }

        return $this->guzzleClient;
    }

    protected function getConfiguration(): Configuration
    {
        if (empty($this->configuration)) {
            $this->configuration = Configuration::getDefaultConfiguration()
                ->setUsername($this->config['username'])
                ->setPassword($this->config['token']);
        }

        return $this->configuration;
    }

    protected function smsMessageCollection(\Core\Sms\SmsMessage $message): SmsMessageCollection
    {
        return tap(new SmsMessageCollection(), function (SmsMessageCollection $collection) use ($message) {
            $collection->setMessages(
                [
                    $this->payload($message)
                ]
            );
        });
    }

    protected function payload(\Core\Sms\SmsMessage $sms): SmsMessage
    {
        return tap(new SmsMessage(), static function (SmsMessage $message) use ($sms) {
            $message->setSource(config('sms.drivers.clicksend.source'));
            $message->setBody($sms->getBody());
            $message->setTo($sms->getTo());
        });
    }

    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function queue(\Core\Sms\SmsMessage $message): void
    {
        $smsable = $message->getSmsable();

        if (isset($smsable->delay)) {
            $this->later($smsable->delay);
        }

        $connection = property_exists($smsable, 'connection') ? $smsable->connection : null;
        $queueName = property_exists($smsable, 'queue') ? $smsable->queue : null;

        $this->queue->connection($connection)->pushOn(
            $queueName ?: null,
            $this->newQueuedJob($message)
        );
    }

    public function later(int $delay): void
    {
    }

    protected function newQueuedJob(\Core\Sms\SmsMessage $message): SendQueuedSms
    {
        return new SendQueuedSms($message, $this);
    }
}
