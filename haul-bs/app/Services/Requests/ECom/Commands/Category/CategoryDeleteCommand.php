<?php

namespace App\Services\Requests\ECom\Commands\Category;

use App\Services\Requests\ECom\EComBaseCommand;
use App\Services\Requests\Exceptions\BeforeRequestCommandException;
use App\Services\Requests\RequestMethodEnum;

class CategoryDeleteCommand extends EComBaseCommand
{
    public function getUri(array $data = null): string
    {
        return str_replace('{id}', $data['id'], config("requests.e_com.paths.category.delete"));
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
