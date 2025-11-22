<?php

namespace App\Services\Fax\Handlers;

use App\Models\Orders\Order;

abstract class SendOrderStatusHandler implements StatusHandler
{

    protected Order $order;

    protected string $messageTo;

    protected string $fileName;

    public function __construct($notification)
    {
        $this->order = $notification->order;
    }

    public function setMessage($message): StatusHandler
    {
        $this->messageTo = $message->getTo();

        return $this;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): StatusHandler
    {
        $this->fileName = $fileName;

        return $this;
    }

    protected function getOrderLoadId(): string
    {
        return $this->getOrder()->load_id;
    }

    protected function getOrder(): Order
    {
        return $this->order;
    }

    protected function getMessageTo(): ?string
    {
        return $this->messageTo ? phone_format($this->messageTo) : null;
    }

}
