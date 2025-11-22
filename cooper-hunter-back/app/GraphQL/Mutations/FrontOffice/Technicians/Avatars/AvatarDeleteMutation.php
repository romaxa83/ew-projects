<?php

namespace App\GraphQL\Mutations\FrontOffice\Technicians\Avatars;

use App\GraphQL\Mutations\Common\Avatars\BaseAvatarDeleteMutation;

class AvatarDeleteMutation extends BaseAvatarDeleteMutation
{
    protected function setMutationGuard(): void
    {
        $this->setTechnicianGuard();
    }
}
