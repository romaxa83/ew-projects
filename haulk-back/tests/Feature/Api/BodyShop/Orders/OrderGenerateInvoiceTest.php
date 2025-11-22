<?php

namespace Api\BodyShop\Orders;

use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Orders\Order;
use App\Models\BodyShop\Orders\TypeOfWork;
use App\Models\BodyShop\Orders\TypeOfWorkInventory;
use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\Locations\State;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class OrderGenerateInvoiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_generate_invoice(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $this->fillSettings();

        $order = $this->createOrder();

        $this->getJson(route('body-shop.orders.generate-invoice', $order))
            ->assertOk();
    }

    public function test_generate_invoice_with_date(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $this->fillSettings();

        $order = $this->createOrder();

        $this->getJson(route('body-shop.orders.generate-invoice', ['invoice_date' => '10/10/2023', 'order' => $order]))
            ->assertOk();
    }

    private function createOrder(): Order
    {
        $vehicleOwner = factory(VehicleOwner::class)->create();
        $truck = factory(Truck::class)->create(['customer_id' => $vehicleOwner->id]);
        $order = factory(Order::class)->create(['truck_id' => $truck->id]);
        $typeOfWork1 = factory(TypeOfWork::class)->create(['order_id' => $order->id]);
        $inventory = factory(Inventory::class)->create();
        factory(TypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork1->id,
            'price' => $inventory->price_retail,
            'inventory_id' => $inventory->id,
            'quantity' => 3,
        ]);
        $inventory = factory(Inventory::class)->create();
        factory(TypeOfWorkInventory::class)->create([
            'type_of_work_id' => $typeOfWork1->id,
            'price' => $inventory->price_retail,
            'inventory_id' => $inventory->id,
            'quantity' => 10,
        ]);
        $typeOfWork2 = factory(TypeOfWork::class)->create(['order_id' => $order->id]);

        return $order;
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

        $attributes = [
            'logo' => UploadedFile::fake()->image('logo.png'),
        ];
        $this->postJson(route('body-shop.settings.upload-info-photo'), $attributes)
            ->assertOk();
    }
}
