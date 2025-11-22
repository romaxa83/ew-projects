<?php

namespace App\Dashboard\Widgets\Chat;

use App\Dashboard\Widgets\AbstractWidget;
use App\Enums\Dashboard\Widgets\DashboardWidgetSectionEnum;
use App\Enums\Dashboard\Widgets\DashboardWidgetTypeEnum;
use App\Models\Admins\Admin;
use App\Repositories\Chat\Conversations\ConversationRepository;
use Core\Chat\Permissions\ChatListPermission;

class MyConversationsWidget extends AbstractWidget
{
    public const PERMISSION = ChatListPermission::KEY;

    public function __construct(protected ConversationRepository $repository)
    {
    }

    public function getTitle(): string
    {
        return __('dashboard.widgets.chat_my_conversations');
    }

    public function getValue(): string
    {
        if (!$this->user instanceof Admin) {
            return '0';
        }

        return $this->repository->myCount($this->user);
    }

    public function getSection(): DashboardWidgetSectionEnum
    {
        return DashboardWidgetSectionEnum::CHAT();
    }

    public function getType(): DashboardWidgetTypeEnum
    {
        return DashboardWidgetTypeEnum::TOTAL();
    }
}