<?php

namespace App\GraphQL\Mutations\FrontOffice\Tires;

use App\GraphQL\Mutations\Common\Tires\BaseTireUpdateMutation;

class TireUpdateMutation extends BaseTireUpdateMutation
{
    protected function setMutationGuard(): void
    {
        $this->setUserGuard();
    }
}
