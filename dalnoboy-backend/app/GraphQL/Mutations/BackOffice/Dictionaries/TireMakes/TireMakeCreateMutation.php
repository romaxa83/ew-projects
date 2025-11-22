<?php

namespace App\GraphQL\Mutations\BackOffice\Dictionaries\TireMakes;

use App\GraphQL\Mutations\Common\Dictionaries\TireMakes\BaseTireMakeCreateMutation;

class TireMakeCreateMutation extends BaseTireMakeCreateMutation
{
    protected function setGuard(): void
    {
        $this->setAdminGuard();
    }
}
