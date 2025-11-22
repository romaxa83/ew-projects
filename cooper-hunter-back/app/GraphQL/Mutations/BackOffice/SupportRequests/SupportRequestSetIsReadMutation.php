<?php

namespace App\GraphQL\Mutations\BackOffice\SupportRequests;

use App\GraphQL\Mutations\Common\SupportRequests\BaseSupportRequestSetIsReadMutation;

class SupportRequestSetIsReadMutation extends BaseSupportRequestSetIsReadMutation
{
    protected function setMutationGuard(): void
    {
        $this->setAdminGuard();
    }
}
