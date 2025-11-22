<?php

namespace App\GraphQL\Queries\FrontOffice\Chat\Conversations;

use Core\Chat\GraphQL\Queries\Conversations\BaseConversationQuery;
use Core\Chat\Permissions\ChatListPermission;

class ConversationQuery extends BaseConversationQuery
{
    public const PERMISSION = ChatListPermission::KEY;

    public function __construct()
    {
        $this->setTechnicianGuard();
    }
}
