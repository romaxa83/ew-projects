<?php

namespace Core\Sms;

use App\Contracts\Members\HasPhoneNumber;
use Core\Contracts\Sms\Smsable;
use Core\Contracts\Sms\SmsTransportContract;
use Core\Sms\Transport\Transport;
use Illuminate\Contracts\Queue\Factory as QueueContract;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;

class SmsSender implements SmsTransportContract
{
    protected string|array $phone;

    protected Transport $transport;
    protected string|Model $to;

    public function to(string|Model $phone): self
    {
        $this->to = $phone;

        return $this;
    }

    public function send(Smsable $smsable): void
    {
        $message = $this->buildMessage($smsable);

        $smsable instanceof ShouldQueue
            ? $this->transport->queue($message)
            : $this->transport->send($message);
    }

    protected function buildMessage(Smsable $smsable): SmsMessage
    {
        return tap(new SmsMessage($smsable), function (SmsMessage $message) {
            $message->setTo($this->getPhone());
        });
    }

    protected function getPhone(): string
    {
        return $this->to instanceof HasPhoneNumber
            ? $this->to->getPhoneString()
            : $this->to;
    }

    public function setQueue(QueueContract $queue): SmsTransportContract
    {
        $this->transport->setQueue($queue);

        return $this;
    }

    public function setTransport(Transport $transport): self
    {
        $this->transport = $transport;

        return $this;
    }
}
