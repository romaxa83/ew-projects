<?php

namespace App\Services\Requests\ECom\Commands\Feature;

use App\Models\Inventories\Features\Feature;
use App\Services\Requests\ECom\EComBaseCommand;
use App\Services\Requests\RequestMethodEnum;

class FeatureUpdateCommand extends EComBaseCommand
{
    public function getUri(array $data = null): string
    {
        $this->assetIdForUri($data, 'guid');

        return str_replace('{id}', $data['guid'], config("requests.e_com.paths.feature.update"));
    }

    public function getMethod(): RequestMethodEnum
    {
        return RequestMethodEnum::Put;
    }

    public function beforeRequestForData(mixed $data): array
    {
        /** @var $data Feature */
        $tmp = [
            'guid' => (string)$data->id,
            'slug' => $data->slug,
            'sort' => (int)$data->position,
            'active' => $data->active,
            'multiple' => $data->multiple,
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
