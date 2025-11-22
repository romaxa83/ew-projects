<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\TireSpecifications;

use App\GraphQL\Mutations\Common\Dictionaries\TireSpecifications\BaseTireSpecificationCreateMutation;

class TireSpecificationCreateMutation extends BaseTireSpecificationCreateMutation
{
    protected function setGuard(): void
    {
        $this->setAdminGuard();;
    }
}
