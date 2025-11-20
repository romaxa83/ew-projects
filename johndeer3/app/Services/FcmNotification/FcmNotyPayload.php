<?php

namespace App\Services\FcmNotification;

class FcmNotyPayload
{
    private $items = [];

    public function setItem(FcmNotyItemPayload $item): void
    {
        $this->items[] = $item;
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
