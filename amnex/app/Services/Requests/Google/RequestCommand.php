<?php

namespace App\Services\Requests\Google;

interface RequestCommand
{
    public function handler(array $data = []);
}
