<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\TireModels;

use App\GraphQL\Mutations\Common\Dictionaries\TireModels\BaseTireModelCreateMutation;

class TireModelCreateMutation extends BaseTireModelCreateMutation
{
    protected function setGuard(): void
    {
        $this->setAdminGuard();
    }
}
