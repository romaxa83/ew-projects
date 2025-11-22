<?php

namespace App\GraphQL\Mutations\BackOffice\Tires;

use App\GraphQL\Mutations\Common\Tires\BaseTireCreateMutation;

class TireCreateMutation extends BaseTireCreateMutation
{
    protected function setGuard(): void
    {
        $this->setAdminGuard();
    }
}
