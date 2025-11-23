<?php

namespace Tests\Feature\Modules\Firebase;


use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Notification;
use Tests\TestCase;
use WezomCms\Firebase\Events\FcmPush;
use WezomCms\Firebase\Models\FcmNotification;
use WezomCms\Firebase\Models\Template;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\OrderStatus;

class OrderNotificationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_creates_fcm_notification_on_change_order_status(): void
    {
        $user = $this->loginAsUser();
        Notification::fake();

        $order = Event::fakeFor(function () use ($user) {
            return Order::factory()->create([
                'user_id' => $user->id,
                'status_id' => OrderStatus::paidStatus(),
            ]);
        });

        event(new FcmPush($user, Template::TYPE_ORDER_CHANGE_STATUS, $order));

        $this->assertDatabaseHas(
            FcmNotification::TABLE,
            [
                'user_id' => $user->id,
                'entity_type' => Order::class,
                'entity_id' => $order->id,
                'status' => FcmNotification::STATUS_ERROR,
                'type' => Template::TYPE_ORDER_CHANGE_STATUS,
                'response_data' => "User [{$user->id}] not have fcm_token",
            ]
        );

        $paidStatus = OrderStatus::paidStatus();
        /** @var Template $template */
        $template = Template::query()->where('type', Template::TYPE_ORDER_CHANGE_STATUS)->first();

        /** @var FcmNotification $notification */
        $notification = FcmNotification::first();

        self::assertEquals($notification->send_data, [
            'text' => $paidStatus->notification_text,
            'title' => str_replace('{order_id}', $order->id, $template->title),
        ]);
    }
}
