<?php

namespace App\GraphQL\Mutations\BackOffice\SupportRequests;

use App\GraphQL\Mutations\Common\SupportRequests\BaseSupportRequestAnswerMutation;

class SupportRequestAnswerMutation extends BaseSupportRequestAnswerMutation
{
    protected function setMutationGuard(): void
    {
        $this->setAdminGuard();
    }
}
