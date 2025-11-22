<?php

namespace App\Services\Requests\ECom\Commands\Inventory;

use App\Models\Inventories\Inventory;
use App\Services\Requests\ECom\EComBaseCommand;
use App\Services\Requests\RequestMethodEnum;

class InventoryExistsCommand extends EComBaseCommand
{
    public function getUri(array $data = null): string
    {
        return config("requests.e_com.paths.inventory.exists");
    }

    public function getMethod(): RequestMethodEnum
    {
        return RequestMethodEnum::Get;
    }

    public function beforeRequestForData(mixed $data): array
    {
        /** @var $data Inventory */

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
