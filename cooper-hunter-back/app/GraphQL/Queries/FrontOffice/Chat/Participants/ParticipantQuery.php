<?php

namespace App\GraphQL\Queries\FrontOffice\Chat\Participants;

use Core\Chat\GraphQL\Queries\Participants\BaseParticipantQuery;
use Core\Chat\Permissions\ChatListPermission;

class ParticipantQuery extends BaseParticipantQuery
{
    public const PERMISSION = ChatListPermission::KEY;

    public function __construct()
    {
        $this->setTechnicianGuard();
    }
}
