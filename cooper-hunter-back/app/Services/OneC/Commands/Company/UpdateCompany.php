<?php

namespace App\Services\OneC\Commands\Company;

use App\Enums\Requests\RequestCommand;

class UpdateCompany extends BaseCompanyCommand
{
    public function nameCommand(): string
    {
        return RequestCommand::UPDATE_COMPANY;
    }

    public function getUri(): string
    {
        return config("api.one_c.request_uri.company.update");
    }
}
