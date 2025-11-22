<?php

use App\Dashboard\Widgets\Chat\MyConversationsWidget;
use App\Dashboard\Widgets\Chat\MyMessagesWidget;
use App\Dashboard\Widgets\NewOrdersWidget;
use App\Dashboard\Widgets\NewQuestionsWidget;
use App\Dashboard\Widgets\NewSupportRequestsWidget;
use App\Dashboard\Widgets\NewWarrantyRegistrationsWidget;
use App\Dashboard\Widgets\TotalOrdersWidget;
use App\Dashboard\Widgets\TotalQuestionsWidget;
use App\Dashboard\Widgets\TotalSupportRequestsWidget;
use App\Dashboard\Widgets\TotalWarrantyRegistrationsWidget;

return [
    /*
     * Register Dashboard widgets in order
     */
    'widgets' => [
        NewOrdersWidget::class,
        TotalOrdersWidget::class,

        NewSupportRequestsWidget::class,
        TotalSupportRequestsWidget::class,

        NewWarrantyRegistrationsWidget::class,
        TotalWarrantyRegistrationsWidget::class,

        NewQuestionsWidget::class,
        TotalQuestionsWidget::class,

        MyConversationsWidget::class,
        MyMessagesWidget::class,
    ],
];
