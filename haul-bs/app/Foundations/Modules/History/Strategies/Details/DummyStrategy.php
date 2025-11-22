<?php

namespace App\Foundations\Modules\History\Strategies\Details;

class DummyStrategy extends BaseDetailsStrategy
{
    public function __construct()
    {}

    public function getDetails(): array
    {
        return [];
    }
}
