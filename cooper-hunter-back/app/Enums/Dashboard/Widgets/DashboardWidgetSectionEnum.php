<?php

namespace App\Enums\Dashboard\Widgets;

use Core\Enums\BaseEnum;

/**
 * @method static static CHAT()
 * @method static static ORDERS()
 * @method static static SUPPORT_REQUESTS()
 * @method static static QUESTIONS()
 * @method static static WARRANTY_REGISTRATIONS()
 */
class DashboardWidgetSectionEnum extends BaseEnum
{
    public const CHAT = 'chat';
    public const ORDERS = 'orders';
    public const SUPPORT_REQUESTS = 'support_requests';
    public const QUESTIONS = 'questions';
    public const WARRANTY_REGISTRATIONS = 'warranty_registrations';
}