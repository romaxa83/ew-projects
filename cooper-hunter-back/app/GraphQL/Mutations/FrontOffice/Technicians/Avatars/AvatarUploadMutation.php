<?php

namespace App\GraphQL\Mutations\FrontOffice\Technicians\Avatars;

use App\GraphQL\Mutations\Common\Avatars\BaseAvatarUploadMutation;

class AvatarUploadMutation extends BaseAvatarUploadMutation
{
    protected function setMutationGuard(): void
    {
        $this->setTechnicianGuard();
    }
}
