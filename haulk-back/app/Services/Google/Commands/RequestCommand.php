<?php

namespace App\Services\Google\Commands;

interface RequestCommand
{
    public function handler(array $data = []);
}

