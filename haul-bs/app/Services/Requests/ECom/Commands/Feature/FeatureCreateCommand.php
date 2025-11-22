<?php

namespace App\Services\Requests\ECom\Commands\Feature;

use App\Models\Inventories\Features\Feature;
use App\Services\Requests\ECom\EComBaseCommand;
use App\Services\Requests\RequestMethodEnum;

class FeatureCreateCommand extends EComBaseCommand
{
    public function getUri(array $data = null): string
    {
        return config("requests.e_com.paths.feature.create");
    }

    public function getMethod(): RequestMethodEnum
    {
        return RequestMethodEnum::Post;
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
