<?php

namespace App\Services\Requests\VAPI;

use App\Services\Requests\BaseCommand;
use App\Services\Requests\RequestClient;
use App\Services\Requests\RequestMethodEnum;

abstract class VapiBaseCommand extends BaseCommand
{
    abstract public function getUri(array $data = null): string;

    abstract public function getMethod(): RequestMethodEnum;

    public function getRequestClient(): RequestClient
    {
        return resolve(VapiClient::class);
    }
}
