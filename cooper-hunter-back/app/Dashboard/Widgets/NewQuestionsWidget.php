<?php

namespace App\Dashboard\Widgets;

use App\Enums\Dashboard\Widgets\DashboardWidgetSectionEnum;
use App\Enums\Dashboard\Widgets\DashboardWidgetTypeEnum;
use App\Models\Faq\Question;
use App\Permissions\SupportRequests\SupportRequestListPermission;

class NewQuestionsWidget extends AbstractWidget
{
    public const PERMISSION = SupportRequestListPermission::KEY;

    public function getTitle(): string
    {
        return __('dashboard.widgets.new_questions');
    }

    public function getValue(): string
    {
        return Question::query()
            ->where('status', 'new')
            ->count();
    }

    public function getSection(): DashboardWidgetSectionEnum
    {
        return DashboardWidgetSectionEnum::QUESTIONS();
    }

    public function getType(): DashboardWidgetTypeEnum
    {
        return DashboardWidgetTypeEnum::NEW();
    }
}