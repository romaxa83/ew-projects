<?php

namespace App\GraphQL\Mutations\FrontOffice\SupportRequests;

use App\GraphQL\Mutations\Common\SupportRequests\BaseSupportRequestAnswerMutation;

class SupportRequestAnswerMutation extends BaseSupportRequestAnswerMutation
{
    protected function setMutationGuard(): void
    {
        $this->setTechnicianGuard();
    }
}
