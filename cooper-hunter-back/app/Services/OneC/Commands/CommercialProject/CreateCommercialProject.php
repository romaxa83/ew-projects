<?php

namespace App\Services\OneC\Commands\CommercialProject;

use App\Enums\Requests\RequestCommand;
use App\Models\Commercial\CommercialProject;
use App\Contracts\Utilities\Dispatchable;

class CreateCommercialProject extends BaseCommercialProject
{
    protected function afterRequest(Dispatchable $model, $response): void
    {
        /** @var $model CommercialProject */
        $model->update(['guid' => data_get($response, 'guid')]);
    }

    public function nameCommand(): string
    {
        return RequestCommand::CREATE_COMMERCIAL_PROJECT;
    }

    public function getUri(): string
    {
        return config("api.one_c.request_uri.commercial_project.create");
    }
}
