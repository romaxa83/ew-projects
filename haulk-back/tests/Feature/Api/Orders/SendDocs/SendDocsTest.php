<?php

namespace Tests\Feature\Api\Orders\SendDocs;

use App\Events\ModelChanged;
use App\Events\Orders\OrderUpdateEvent;
use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use App\Notifications\Orders\SendDocs;
use App\Services\Fax\Drivers\Fake\FakeFaxDriver;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Helpers\Traits\FaxSettingFactoryHelper;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\Helpers\Traits\SettingFactoryHelper;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class SendDocsTest extends TestCase
{
    use DatabaseTransactions;
    use FaxSettingFactoryHelper;
    use SettingFactoryHelper;
    use OrderFactoryHelper;
    use UserFactoryHelper;

    public function test_fails_empty_data(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $this->postJson(route('orders.send-docs'))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonCount(3, 'errors');
    }

    /**
     * @throws Exception
     */
    public function test_send_both_to_email(): void
    {
        $this->billingEmailFactory();

        $faxNumber = '123456798';
        $this->faxSettingFactory($faxNumber);

        Notification::fake();
        Event::fake(
            [
                OrderUpdateEvent::class,
                ModelChanged::class
            ]
        );

        $this->loginAsCarrierSuperAdmin();

        $order = $this->generateOrder();

        $email = 'test@mail.net';
        $fax = '+123456789012';

        $this->postJson(
            route('orders.send-docs'),
            [
                'send_via' => [
                    'email',
                    'fax'
                ],
                'recipient_email' => [
                    [
                        'value' => $email
                    ],
                ],
                'recipient_fax' => $fax,
                'invoice_recipient' => Payment::PAYER_CUSTOMER,
                'orders' => [
                    [
                        'id' => $order->id,
                        'invoice_id' => '123ID',
                        'invoice_date' => '12/10/2020'
                    ],
                ],
                'content' => [
                    'bol',
                    'invoice'
                ],
            ]
        )
            ->assertOk()
            ->assertJsonPath('data.message', 'Success');

        Event::assertDispatched(OrderUpdateEvent::class);
        Event::assertDispatched(ModelChanged::class, 2);
        Notification::assertSentTo(new AnonymousNotifiable(), SendDocs::class);
    }

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
    public function test_it_send_only_invoice_file(): void
    {
        $this->billingEmailFactory();

        $faxNumber = '123456798';
        $this->faxSettingFactory($faxNumber);

        Notification::fake();
        Event::fake(
            [
                OrderUpdateEvent::class,
                ModelChanged::class
            ]
        );

        $this->loginAsCarrierSuperAdmin();

        $order = $this->generateOrder();
        $email = 'test@mail.net';
        $fax = '+123456789012';

        $this->postJson(
            route('orders.send-docs'),
            [
                'send_via' => [
                    'fax',
                    'email'
                ],

                'recipient_email' => [
                    [
                        'value' => $email
                    ],
                ],
                'recipient_fax' => $fax,
                'invoice_recipient' => Payment::PAYER_CUSTOMER,
                'orders' => [
                    [
                        'id' => $order->id,
                        'invoice_id' => '123ID',
                        'invoice_date' => '12/10/2020'
                    ],
                ],
                'content' => [
                    'invoice'
                ],
            ]
        )
            ->assertOk()
            ->assertJsonPath('data.message', 'Success');

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'id' => $order->id,
                'is_billed' => true,
            ]
        );

        Event::assertDispatched(OrderUpdateEvent::class);
        Event::assertDispatched(ModelChanged::class, 2);
        Notification::assertSentTo(new AnonymousNotifiable(), SendDocs::class);
    }

    /**
     * @throws Exception
     */
    public function test_it_send_only_bol_file(): void
    {
        $this->billingEmailFactory();

        $faxNumber = '123456798';
        $this->faxSettingFactory($faxNumber);

        Notification::fake();
        $this->loginAsCarrierSuperAdmin();

        $order = $this->generateOrder();
        $email = 'test@mail.net';
        $fax = '+123456789012';

        $this->postJson(
            route('orders.send-docs'),
            [
                'send_via' => [
                    'fax',
                    'email'
                ],

                'recipient_email' => [
                    [
                        'value' => $email
                    ],
                ],
                'recipient_fax' => $fax,
                'orders' => [
                    [
                        'id' => $order->id
                    ],
                ],
                'content' => [
                    'bol'
                ],
            ]
        )
            ->assertOk()
            ->assertJsonPath('data.message', 'Success');

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'id' => $order->id,
                'is_billed' => false,
            ]
        );

        Notification::assertSentTo(new AnonymousNotifiable(), SendDocs::class);
    }

    /**
     * @throws Exception
     */
    public function test_send_both_only_via_fax(): void
    {
        Notification::fake();

        $this->billingEmailFactory();

        $faxNumber = '123456798';
        $this->faxSettingFactory($faxNumber);

        $this->loginAsCarrierSuperAdmin();

        $order = $this->generateOrder();

        $fax = '+123456789012';

        $this->postJson(
            route('orders.send-docs'),
            [
                'recipient_fax' => $fax,
                'send_via' => [
                    'fax'
                ],
                'invoice_recipient' => Payment::PAYER_CUSTOMER,
                'orders' => [
                    [
                        'id' => $order->id,
                        'invoice_id' => '123ID',
                        'invoice_date' => '12/10/2020'
                    ],
                ],
                'content' => [
                    'invoice',
                    'bol'
                ],
            ]
        )
            ->assertOk()
            ->assertJsonPath('data.message', 'Success');

        Notification::assertSentTo(
            new AnonymousNotifiable(),
            SendDocs::class,
            function ($notification, $channels, $notifiable) use ($fax) {
                return $notifiable->routes['fax'] === $fax;
            }
        );

        Notification::assertSentTo(
            new AnonymousNotifiable(),
            SendDocs::class,
            function ($notification, $channels, $notifiable) {
                $this->assertArrayNotHasKey('mail', $notifiable->routes);
                return true;
            }
        );
    }

    /**
     * @throws Exception
     */
    public function test_send_all_only_via_email(): void
    {
        Notification::fake();

        $this->billingEmailFactory();

        $this->loginAsCarrierSuperAdmin();

        $order = $this->generateOrder();

        $email = 'test@mail.net';

        $this->postJson(
            route('orders.send-docs'),
            [
                'recipient_email' => [
                    [
                        'value' => $email
                    ],
                ],
                'send_via' => [
                    'email'
                ],
                'invoice_recipient' => Payment::PAYER_CUSTOMER,
                'orders' => [
                    [
                        'id' => $order->id,
                        'invoice_id' => '123ID',
                        'invoice_date' => '12/10/2020'
                    ],
                ],
                'content' => [
                    'bol',
                    'invoice'
                ],
            ]
        )
            ->assertOk()
            ->assertJsonPath('data.message', 'Success');

        Notification::assertSentTo(
            new AnonymousNotifiable(),
            SendDocs::class,
            function ($notification, $channels, $notifiable) {
                $this->assertArrayNotHasKey('fax', $notifiable->routes);

                return true;
            }
        );

        Notification::assertSentTo(
            new AnonymousNotifiable(),
            SendDocs::class,
            function ($notification, $channels, $notifiable) use ($email) {
                if (is_array($notifiable->routes['mail'])) {
                    return in_array($email, $notifiable->routes['mail'], true);
                }

                return $email === $notifiable->routes['mail'];
            }
        );
    }

    /**
     * @throws Exception
     */
    public function test_send_w9_to_email_without_w9(): void
    {
        $this->billingEmailFactory();

        Notification::fake();
        $this->loginAsCarrierSuperAdmin();

        $order = $this->generateOrder();

        $email = 'test@mail.net';

        $this->postJson(
            route('orders.send-docs'),
            [
                'send_via' => [
                    'email'
                ],
                'recipient_email' => [
                    [
                        'value' => $email
                    ],
                ],
                'orders' => [
                    [
                        'id' => $order->id
                    ],
                ],
                'content' => [
                    'w9'
                ],
            ]
        )
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonCount(2, 'errors');
    }

    /**
     * @throws Exception
     */
    public function test_send_w9_to_email(): void
    {
        $this->billingEmailFactory();

        Notification::fake();

        $this->loginAsCarrierSuperAdmin();

        $this->postJson(
            route('carrier.upload-w9-photo'),
            [
                'w9_form_image' => UploadedFile::fake()->image('w9.png')
            ]
        )
            ->assertOk();

        $order = $this->generateOrder();

        $email = 'test@mail.net';

        Storage::shouldReceive('exists')->andReturnTrue();
        Storage::shouldReceive('get')->andReturnSelf();

        $this->postJson(
            route('orders.send-docs'),
            [
                'send_via' => [
                    'email'
                ],
                'recipient_email' => [
                    [
                        'value' => $email
                    ],
                ],
                'orders' => [
                    [
                        'id' => $order->id,
                    ],
                ],
                'content' => [
                    'w9'
                ],
            ]
        )
            ->assertOk()
            ->assertJsonPath('data.message', 'Success');

        Notification::assertSentTo(new AnonymousNotifiable(), SendDocs::class);
    }

    public function test_it_send_broker_payment_planned_date_changed_after_invoice_send(): void
    {
        $this->billingEmailFactory();

        $faxNumber = '123456798';
        $this->faxSettingFactory($faxNumber);

        Notification::fake();
        Event::fake(
            [
                OrderUpdateEvent::class,
                ModelChanged::class
            ]
        );

        $this->loginAsCarrierSuperAdmin();

        $order = $this->generateOrder();
        $order->payment->broker_payment_amount = 100;
        $order->payment->broker_payment_days = 1;
        $order->payment->broker_payment_begins = Order::INVOICE_SENT;
        $order->payment->save();

        $email = 'test@mail.net';
        $fax = '+123456789012';

        $this->postJson(
            route('orders.send-docs'),
            [
                'send_via' => [
                    'fax',
                    'email'
                ],

                'recipient_email' => [
                    [
                        'value' => $email
                    ],
                ],
                'recipient_fax' => $fax,
                'invoice_recipient' => Payment::PAYER_BROKER,
                'orders' => [
                    [
                        'id' => $order->id,
                        'invoice_id' => '123ID',
                        'invoice_date' => '12/10/2020'
                    ],
                ],
                'content' => [
                    'invoice'
                ],
            ]
        )
            ->assertOk()
            ->assertJsonPath('data.message', 'Success');

        $this->assertDatabaseHas(
            Order::TABLE_NAME,
            [
                'id' => $order->id,
                'is_billed' => true,
            ]
        );

        $order->payment->refresh();
        $this->assertNotNull($order->payment->broker_payment_planned_date);

        Event::assertDispatched(OrderUpdateEvent::class);
        Event::assertDispatched(ModelChanged::class, 2);
        Notification::assertSentTo(new AnonymousNotifiable(), SendDocs::class);
    }


    protected function setUp(): void
    {
        parent::setUp();

        FakeFaxDriver::clear();
    }
}
