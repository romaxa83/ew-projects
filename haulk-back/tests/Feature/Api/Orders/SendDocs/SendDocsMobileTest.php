<?php

namespace Tests\Feature\Api\Orders\SendDocs;

use App\Events\ModelChanged;
use App\Events\Orders\OrderUpdateEvent;
use App\Models\Orders\Order;
use App\Models\SendDocsDelay;
use App\Notifications\Orders\SendDocs;
use App\Services\Orders\GeneratePdfService;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\Helpers\Traits\FaxSettingFactoryHelper;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\Helpers\Traits\SettingFactoryHelper;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class SendDocsMobileTest extends TestCase
{
    use DatabaseTransactions;
    use FaxSettingFactoryHelper;
    use SettingFactoryHelper;
    use OrderFactoryHelper;
    use UserFactoryHelper;

    private const RECIPIENT_EMAIL = 'recipient.email@gmail.com';

    protected function generateOrder(): Order
    {
        $driver = $this->driverFactory();
        $dispatcher = $this->dispatcherFactory();

        $order = $this->orderFactory(
            [
                'status' => Order::STATUS_NEW,
                'driver_id' => $driver->id,
                'dispatcher_id' => $dispatcher->id,
            ]
        );

        $this->createOrderPayment($order->id, 1000);

        return $order;
    }

    /**
     * @throws Exception
     */
    public function test_send_docs_immediately(): void
    {
        $this->billingEmailFactory();

        Notification::fake();
        Event::fake(
            [
                OrderUpdateEvent::class,
                ModelChanged::class
            ]
        );

        $order = $this->generateOrder();

        $this->loginAsCarrierDriver($order->driver);


        $this->putJson(
            route('mobile.orders.send-docs', $order),
            [
                'recipient_email' => self::RECIPIENT_EMAIL,
                'content' => 'invoice',
            ]
        )
            ->assertOk()
            ->assertJsonPath('data.message', 'Success');

        Event::assertDispatched(OrderUpdateEvent::class);
        Event::assertDispatched(ModelChanged::class, 1);
        Notification::assertSentTo(new AnonymousNotifiable(), SendDocs::class);
    }

    private function sendDocsDelay(Order $order, string $inspection)
    {
        if ($inspection === 'pickup') {
            $order->status = Order::STATUS_PICKED_UP;
            $order->has_pickup_signature = true;
            $order->has_pickup_inspection = true;
        } else {
            $order->status = Order::STATUS_DELIVERED;
            $order->has_delivery_signature = true;
            $order->has_delivery_inspection = true;
        }

        $order->save();

        $generatePdfService = resolve(GeneratePdfService::class);

        Event::fake();
        Notification::fake();

        $generatePdfService->sendDocsDelayed($order, $inspection);

        Event::assertDispatched(OrderUpdateEvent::class);
        Event::assertDispatched(ModelChanged::class, 1);

        Notification::assertSentTo(
            new AnonymousNotifiable,
            SendDocs::class,
            function (SendDocs $notification, array $channels, AnonymousNotifiable $notifiable) use ($order){
                $this->assertEquals($notification->order->id, $order->id);
                $this->assertContains(SendDocs::ATTACHMENT_CUSTOMER_INVOICE, $notification->attachments());

                $this->assertArrayNotHasKey('fax', $notifiable->routes);
                $this->assertArrayHasKey('mail', $notifiable->routes);

                $emails = $notifiable->routes['mail'];

                if (is_array($emails)) {
                    $this->assertContains(self::RECIPIENT_EMAIL, $emails);
                } else {
                    $this->assertEquals(self::RECIPIENT_EMAIL, $emails);
                }

                return true;
            }
        );

    }

    /**
     * @throws Exception
     */
    public function test_send_docs_after_pickup(): void
    {
        $this->billingEmailFactory();

        Notification::fake();
        Event::fake(
            [
                OrderUpdateEvent::class,
                ModelChanged::class
            ]
        );

        $order = $this->generateOrder();

        $order->save();

        $this->loginAsCarrierDriver($order->driver);

        $this->putJson(
            route('mobile.orders.send-docs', $order),
            [
                'recipient_email' => self::RECIPIENT_EMAIL,
                'content' => 'invoice',
                'after_inspection' => 'pickup'
            ]
        )
            ->assertOk()
            ->assertJsonPath('data.message', 'Success');

        Event::assertNotDispatched(OrderUpdateEvent::class);
        Event::assertNotDispatched(ModelChanged::class);
        Notification::assertNotSentTo(new AnonymousNotifiable(), SendDocs::class);

        $this->assertDatabaseHas(
            SendDocsDelay::class,
            [
                'order_id' => $order->id,
                'sender_id' => $order->driver->id,
                'inspection_type' => 'pickup'
            ]
        );

        $this->sendDocsDelay($order, 'pickup');
    }

    /**
     * @throws Exception
     */
    public function test_send_docs_after_delivery(): void
    {
        $this->billingEmailFactory();

        Notification::fake();
        Event::fake(
            [
                OrderUpdateEvent::class,
                ModelChanged::class
            ]
        );

        $order = $this->generateOrder();

        $order->save();

        $this->loginAsCarrierDriver($order->driver);

        $this->putJson(
            route('mobile.orders.send-docs', $order),
            [
                'recipient_email' => self::RECIPIENT_EMAIL,
                'content' => 'invoice',
                'after_inspection' => 'delivery'
            ]
        )
            ->assertOk()
            ->assertJsonPath('data.message', 'Success');

        Event::assertNotDispatched(OrderUpdateEvent::class);
        Event::assertNotDispatched(ModelChanged::class);
        Notification::assertNotSentTo(new AnonymousNotifiable(), SendDocs::class);

        $this->assertDatabaseHas(
            SendDocsDelay::class,
            [
                'order_id' => $order->id,
                'sender_id' => $order->driver->id,
                'inspection_type' => 'delivery'
            ]
        );

        $this->sendDocsDelay($order, 'delivery');
    }
}
