<?php

namespace App\Services\Requests\ECom\Commands\Inventory;

use App\Services\Requests\ECom\EComBaseCommand;
use App\Services\Requests\Exceptions\BeforeRequestCommandException;
use App\Services\Requests\RequestMethodEnum;

class InventoryDeleteCommand extends EComBaseCommand
{
    public function getUri(array $data = null): string
    {
        return str_replace('{id}', $data['id'], config("requests.e_com.paths.inventory.delete"));
    }

    public function getMethod(): RequestMethodEnum
    {
        return RequestMethodEnum::Delete;
    }

    public function beforeRequestForData(mixed $data): array
    {
        $this->assetIdForUri($data);

        return $data;
    }
}
