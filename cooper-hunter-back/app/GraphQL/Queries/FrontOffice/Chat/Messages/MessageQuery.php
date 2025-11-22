<?php

namespace App\GraphQL\Queries\FrontOffice\Chat\Messages;

use Core\Chat\GraphQL\Queries\Messages\BaseMessageQuery;
use Core\Chat\Permissions\ChatMessagingPermission;

class MessageQuery extends BaseMessageQuery
{
    public const PERMISSION = ChatMessagingPermission::KEY;

    public function __construct()
    {
        $this->setTechnicianGuard();
    }
}
