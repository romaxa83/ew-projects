<?php

namespace App\GraphQL\Mutations\BackOffice\Admins\Avatars;

use App\GraphQL\Mutations\Common\Avatars\BaseAvatarDeleteMutation;

class AvatarDeleteMutation extends BaseAvatarDeleteMutation
{
    protected function setMutationGuard(): void
    {
        $this->setAdminGuard();
    }
}
