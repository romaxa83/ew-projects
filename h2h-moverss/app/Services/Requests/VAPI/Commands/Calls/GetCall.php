<?php

namespace App\Services\Requests\VAPI\Commands\Calls;

use App\Services\Requests\RequestMethodEnum;
use App\Services\Requests\VAPI\VapiBaseCommand;
use App\Services\Telegram\Telegram;
use Carbon\CarbonImmutable;

class GetCall extends VapiBaseCommand
{
    public function __construct()
    {
    }

    public function getUri(array $data = null): string
    {
        return str_replace('{id}', $data['id'], config("requests.vapi.paths.call"));
    }

    public function getMethod(): RequestMethodEnum
    {
        return RequestMethodEnum::Get;
    }

    public function beforeRequestForHeaders(array $headers): array
    {
        return $headers;
    }

    public function beforeRequestForData(mixed $data): array
    {
        return $data;
    }

    public function afterRequest(array $res): mixed
    {
        return $res;
    }

    protected function handlerRequestException(\Throwable $e, array $data, array $headers)
    {
        Telegram::error($e->getMessage(), \Auth::user()?->email, [
            'url' => 'GetAssistants Command by VAPI',
            'file' => $e->getFile() . ' ['. $e->getLine() .']',
            'time' => CarbonImmutable::now()->format('Y-m-d H:i:s') . ' UTC',
            'browser' => \Request::header('User-Agent', 'cli'),
            'data' => $data,
            'headers' => $headers,
        ]);
    }
}

