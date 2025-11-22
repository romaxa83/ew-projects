<?php

namespace App\Services\Requests\ECom\Commands\Settings;

use App\Http\Resources\Settings\SettingsEcomInfoResource;
use App\Services\Requests\ECom\EComBaseCommand;
use App\Services\Requests\RequestMethodEnum;

class SettingsUpdateCommand extends EComBaseCommand
{
    public function getUri(array $data = null): string
    {
        return config("requests.e_com.paths.settings.update");
    }

    public function getMethod(): RequestMethodEnum
    {
        return RequestMethodEnum::Put;
    }

    public function beforeRequestForData(mixed $data): array
    {
        $tmp = SettingsEcomInfoResource::make($data);

        return $tmp->toArray($tmp);
    }

    public function afterRequest(array $res): mixed
    {
        return $res['data'];
    }
}
