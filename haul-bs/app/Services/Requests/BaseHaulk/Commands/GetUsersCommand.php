<?php

namespace App\Services\Requests\BaseHaulk\Commands;

use App\Services\Requests\BaseHaulk\HaulkBaseCommand;
use App\Services\Requests\RequestMethodEnum;

class GetUsersCommand extends HaulkBaseCommand
{
    public function getUri(array $data = null): string
    {
        return config("requests.base_haulk.paths.get_users");
    }

    public function getMethod(): RequestMethodEnum
    {
        return RequestMethodEnum::Get;
    }

    public function afterRequest(array $res): mixed
    {
        return $res['data'];
    }
}
