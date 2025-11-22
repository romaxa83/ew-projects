<?php

namespace App\Contracts\Alerts;

interface AlertModel
{
    public function getId(): int;

    public function getMorphType(): string;
}
