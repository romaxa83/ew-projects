<?php

namespace App\GraphQL\Queries\BackOffice\Chat\Messages;

use Core\Chat\Facades\Chat;
use Core\Chat\GraphQL\Queries\Messages\BaseMessageQuery;
use Core\Chat\Models\Conversation;

class MessageQuery extends BaseMessageQuery
{
    public function __construct()
    {
        $this->setAdminGuard();
    }

    protected function getConversation(int $conversationId): Conversation
    {
        return Chat::conversations()
            ->findForAdministratorOrFail($conversationId);
    }
}
