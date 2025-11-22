<?php

namespace App\Services\SendPulse\Commands;

interface RequestCommand
{
    public function handler(array $data = []);
}
