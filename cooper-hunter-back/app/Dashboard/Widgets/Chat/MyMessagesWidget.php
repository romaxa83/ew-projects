<?php

namespace App\Dashboard\Widgets\Chat;

use App\Dashboard\Widgets\AbstractWidget;
use App\Enums\Dashboard\Widgets\DashboardWidgetSectionEnum;
use App\Enums\Dashboard\Widgets\DashboardWidgetTypeEnum;
use Core\Chat\Contracts\Messageable;
use Core\Chat\Facades\Chat;
use Core\Chat\Permissions\ChatListPermission;
use Throwable;

class MyMessagesWidget extends AbstractWidget
{
    public const PERMISSION = ChatListPermission::KEY;

    public function getTitle(): string
    {
        return __('dashboard.widgets.chat_my_messages');
    }

    /**
     * @throws Throwable
     */
    public function getValue(): string
    {
        if (!$this->user instanceof Messageable) {
            return '0';
        }

        return Chat::conversations()
            ->getUnreadCount($this->user);
    }

    public function getSection(): DashboardWidgetSectionEnum
    {
        return DashboardWidgetSectionEnum::CHAT();
    }

    public function getType(): DashboardWidgetTypeEnum
    {
        return DashboardWidgetTypeEnum::NEW();
    }
}