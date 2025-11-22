<?php

namespace App\Services\OneC\Commands\CommercialProject;

use App\Enums\Requests\RequestCommand;

class UpdateCommercialProject extends BaseCommercialProject
{
    public function nameCommand(): string
    {
        return RequestCommand::UPDATE_COMMERCIAL_PROJECT;
    }

    public function getUri(): string
    {
        return config("api.one_c.request_uri.commercial_project.update");
    }
}
