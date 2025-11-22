<?php

namespace Api\BodyShop\Orders;

use App\Models\BodyShop\Orders\Order;
use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\Locations\State;
use App\Models\Vehicles\Truck;
use App\Notifications\BodyShop\Orders\SendDocs;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class SendDocsTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_fails_empty_data(): void
    {
        $this->loginAsBodyShopSuperAdmin();
        $order = factory(Order::class)->create();

        $this->postJson(route('body-shop.orders.send-docs', $order))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @throws Exception
     */
    public function test_it_send_only_invoice_file(): void
    {
        Notification::fake();
        Event::fake();

        $this->loginAsBodyShopSuperAdmin();
        $this->fillSettings();

        $vehicleOwner = factory(VehicleOwner::class)->create();
        $truck = factory(Truck::class)->create(['customer_id' => $vehicleOwner->id]);
        $order = factory(Order::class)->create(['truck_id' => $truck->id]);
        $email = 'test@mail.net';

        $order->refresh();
        $this->assertFalse($order->is_billed);
        $this->assertNull($order->billed_at);

        $this->postJson(
            route('body-shop.orders.send-docs', $order),
            [
                'recipient_email' => [
                    $email
                ],
                'invoice_date' => '12/10/2020',
                'content' => [
                    'invoice'
                ],
            ]
        )
            ->assertOk()
            ->assertJsonPath('data.message', 'Success');

        Notification::assertSentTo(new AnonymousNotifiable(), SendDocs::class);

        $order->refresh();
        $this->assertTrue($order->is_billed);
        $this->assertNotNull($order->billed_at);
    }

    private function fillSettings()
    {
        $state = factory(State::class)->create();
        $formRequest = [
            'company_name' => 'Test name',
            'address' => 'test address',
            'city' => 'test city',
            'state_id' => $state->id,
            'zip' => '3454',
            'timezone' => 'America/Los_Angeles',
            'phone' => '3456723455',
            'phone_name' => 'test',
            'phone_extension' => 'ext',
            'phones' => [
                [
                    'name' => 'test 1',
                    'number' => '3443423423',
                    'extension' => '34'
                ],
            ],
            'email' => 'test@test.com',
            'fax' => '35345435',
            'website' => 'test.com',
            'billing_phone' => '435345345345',
            'billing_phone_name' => 'test bill',
            'billing_phone_extension' => 'test bill ext',
            'billing_phones' => [],
            'billing_email' => 'test@test.coom',
            'billing_payment_details' => 'text',
            'billing_terms' => 'terms text',
        ];


        $this->loginAsBodyShopSuperAdmin();

        $this->putJson(route('body-shop.settings.update-info'), $formRequest)
            ->assertOk();
    }
}
