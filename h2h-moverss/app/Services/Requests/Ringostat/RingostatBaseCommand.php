<?php

namespace App\Services\Requests\Ringostat;

use App\Services\Requests\BaseCommand;
use App\Services\Requests\RequestClient;
use App\Services\Requests\RequestMethodEnum;

abstract class RingostatBaseCommand extends BaseCommand
{
    abstract public function getUri(array $data = null): string;

    abstract public function getMethod(): RequestMethodEnum;

    public function getRequestClient(): RequestClient
    {
        return resolve(RingostatRequestClient::class);
    }
}
