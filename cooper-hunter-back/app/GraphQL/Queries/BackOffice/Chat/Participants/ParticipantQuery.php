<?php

namespace App\GraphQL\Queries\BackOffice\Chat\Participants;

use Core\Chat\GraphQL\Queries\Participants\BaseParticipantQuery;

class ParticipantQuery extends BaseParticipantQuery
{
    public function __construct()
    {
        $this->setAdminGuard();
    }
}
