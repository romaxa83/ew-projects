<?php

namespace App\Notifications\Messages;

use App\Models\Orders\Order;
use Illuminate\Notifications\Messages\SimpleMessage;

class FaxMessage extends SimpleMessage
{
    private Order $order;

    private string $from;

    private array $rawAttachments;

    public function attachData($data, $name, array $options = []): self
    {
        $this->rawAttachments[] = compact('data', 'name', 'options');

        return $this;
    }

    public function getRawAttachments(): array
    {
        return $this->rawAttachments;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function setFrom(string $from): self
    {
        $this->from = $from;

        return $this;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): self
    {
        $this->order = $order;

        return $this;
    }

}
