<?php

namespace App\Services\Requests\ECom\Commands\Category;

use App\Models\Inventories\Category;
use App\Services\Requests\ECom\EComBaseCommand;
use App\Services\Requests\RequestMethodEnum;

class CategoryExistsCommand extends EComBaseCommand
{
    public function getUri(array $data = null): string
    {
        return config("requests.e_com.paths.category.exists");
    }

    public function getMethod(): RequestMethodEnum
    {
        return RequestMethodEnum::Get;
    }

    public function beforeRequestForData(mixed $data): array
    {
        /** @var $data Category */

        $tmp = [
            'guid' => (string)$data->id,
        ];

        return $tmp;
    }

    public function afterRequest(array $res): mixed
    {
        return $res['exists'];
    }
}
