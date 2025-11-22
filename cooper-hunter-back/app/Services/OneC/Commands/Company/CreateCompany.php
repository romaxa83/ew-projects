<?php

namespace App\Services\OneC\Commands\Company;

use App\Contracts\Utilities\Dispatchable;
use App\Enums\Requests\RequestCommand;
use App\Models\Companies\Company;
use Ramsey\Uuid\Uuid;

class CreateCompany extends BaseCompanyCommand
{
    public function nameCommand(): string
    {
        return RequestCommand::CREATE_COMPANY;
    }

    public function getUri(): string
    {

        return config("api.one_c.request_uri.company.create");
    }

    protected function afterRequest(Dispatchable $model, $response): void
    {
        /** @var $model Company */
        $model->update(['guid' => data_get($response, 'guid')]);
    }
}
