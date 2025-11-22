<?php

namespace App\GraphQL\Mutations\BackOffice\Admins\Avatars;

use App\GraphQL\Mutations\Common\Avatars\BaseAvatarUploadMutation;

class AvatarUploadMutation extends BaseAvatarUploadMutation
{
    protected function setMutationGuard(): void
    {
        $this->setAdminGuard();
    }
}
