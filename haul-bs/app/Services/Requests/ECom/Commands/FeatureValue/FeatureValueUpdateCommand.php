<?php

namespace App\Services\Requests\ECom\Commands\FeatureValue;

use App\Models\Inventories\Features\Value;
use App\Services\Requests\ECom\EComBaseCommand;
use App\Services\Requests\Exceptions\BeforeRequestCommandException;
use App\Services\Requests\Exceptions\RequestCommandException;
use App\Services\Requests\RequestMethodEnum;

class FeatureValueUpdateCommand extends EComBaseCommand
{
    public function getUri(array $data = null): string
    {
        $this->assetIdForUri($data, 'guid');

        return str_replace('{id}', $data['guid'], config("requests.e_com.paths.feature_value.update"));
    }

    public function getMethod(): RequestMethodEnum
    {
        return RequestMethodEnum::Put;
    }

    public function beforeRequestForData(mixed $data): array
    {
        /** @var $data Value */
        $tmp = [
            'guid' => (string)$data->id,
            'slug' => $data->slug,
            'sort' => (int)$data->position,
            'active' => $data->active,
            'specification_guid' => (string)$data->feature_id,
            'translations' => [
                [
                    'language' => 'en',
                    'name' => $data->name,
                ]
            ]
        ];

        return $tmp;
    }

    public function afterRequest(array $res): mixed
    {
        return $res['data'];
    }
}
