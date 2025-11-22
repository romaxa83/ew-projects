<?php

namespace App\Services\Requests\ECom\Commands\Inventory;

use App\Models\Inventories\Inventory;
use App\Services\Requests\ECom\EComBaseCommand;
use App\Services\Requests\RequestMethodEnum;

class InventoryChangeQuantityCommand extends EComBaseCommand
{
    public function getUri(array $data = null): string
    {
        $this->assetIdForUri($data, 'guid');

        return str_replace('{id}', $data['guid'], config("requests.e_com.paths.inventory.update_quantity"));
    }

    public function getMethod(): RequestMethodEnum
    {
        return RequestMethodEnum::Put;
    }

    public function beforeRequestForData(mixed $data): array
    {
        /** @var $data Inventory */

        $tmp = [
            'guid' => (string)$data->id,
            'quantity' => (float)$data->quantity,
        ];

        return $tmp;
    }

    public function afterRequest(array $res): mixed
    {
        return $res['data'];
    }
}
