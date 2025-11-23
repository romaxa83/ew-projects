<?php

namespace Tests\Feature\Modules\Orders\V1\Delivery;

use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;
use WezomCms\Catalog\Models\Product;
use WezomCms\Core\Models\Administrator;
use WezomCms\Orders\Cart\Storage\DatabaseStorage;
use WezomCms\Orders\Contracts\CartInterface;
use WezomCms\Orders\Drivers\Delivery\SdekCourier;
use WezomCms\Orders\Drivers\Delivery\SdekPostal;
use WezomCms\Orders\Events\CreatedOrders;
use WezomCms\Orders\Models\Delivery;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\Payment;
use WezomCms\Providers\Models\Provider;
use WezomCms\Providers\Types\ProviderStatus;

class DeliveryTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    private function createModels(): void
    {
        Delivery::factory()->create(['driver' => SdekCourier::KEY]);
    }

    public function test_it_returns_list_of_deliveries(): void
    {
        $deliveries = Delivery::factory()
            ->count(3)
            ->state(new Sequence(
                [],
                ['driver' => SdekCourier::KEY],
                ['driver' => SdekPostal::KEY],
            ))
            ->create();
        Delivery::factory()->create([ 'published' => false ]);

        $res = $this->getJson(route('api.v1.mobile.delivery-drivers'))
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure($this->structureResource([
                "*" => [
                    "id",
                    "sort",
                    "driver",
                ]
            ]));

        $deliveriesData = $deliveries
            ->sortBy(function (Delivery $delivery) {
                return $delivery->sort;
            })
            ->map(function (Delivery $delivery) {
                return $delivery->only(['id', 'sort', 'driver', 'name']);
            })
            ->values()
            ->toArray();

        self::assertEquals(
            $deliveriesData,
            $res->json('data'),
        );
    }

    /*public function test_it_returns_list_of_regions(): void
    {
        $res = $this->getJson(route('api.v1.mobile.sdek.regions'))
            ->assertOk()
            ->assertJsonStructure($this->structureResource([
                '*' => [
                    'country',
                    'country_code',
                    'region',
                    'region_code',
                ]
            ]));

        $countryCodes = collect($res->json('data'))
            ->pluck('country_code')
            ->unique();

        self::assertCount(1, $countryCodes);
        self::assertEquals('KZ', $countryCodes->first());
    }*/

    /*public function test_it_returns_full_list_of_region_settlements(): void
    {
        $regionCode = 492;

        $res = $this->getJson(route('api.v1.mobile.sdek.cities', [ 'region' => $regionCode ]))
            ->assertOk()
            ->assertJsonCount(6, 'data')
            ->assertJsonStructure($this->structureResource([
                '*' => [
                    'code',
                    'country_code',
                    'region_code',
                    'city',
                ]
            ]));

        $regionCodes = collect($res->json('data'))
            ->pluck('region_code')
            ->unique();

        self::assertCount(1, $regionCodes);
        self::assertEquals($regionCode, $regionCodes->first());
    }*/

    /*public function test_it_returns_limited_list_of_region_settlements(): void
    {
        $regionCode = 299;
        $limit = 10;

        $this->getJson(route('api.v1.mobile.sdek.cities', [
            'region' => $regionCode,
            'limit' => $limit,
        ]))
            ->assertOk()
            ->assertJsonCount($limit, 'data');
    }*/

    /*public function test_it_returns_list_of_region_settlements_filtered_by_city_name(): void
    {
        $regionCode = 299;
        $query = 'Талды';

        $res = $this->getJson(route('api.v1.mobile.sdek.cities', [
            'region' => $regionCode,
            'query' => $query,
        ]))
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $cities = $res->json('data');

        self::assertTrue(collect($cities)->contains(function ($city) {
            return $city['city'] === 'Талдыкорган';
        }));
    }*/

    /*public function test_it_returns_list_of_delivery_points_by_city_code(): void
    {
        $cityCode = 4756;

        $this->getJson(route('api.v1.mobile.sdek.delivery-points', [
            'city' => $cityCode,
        ]))
            ->assertOk()
            ->assertJsonStructure($this->structureResource([
                '*' => [
                    'name',
                    'code',
                    'location' => [
                        'city_code',
                        'postal_code',
                        'country_code',
                        'region',
                        'region_code',
                        'sub_region',
                        'city',
                        'address',
                    ],
                    'work_time',
                    'note',
                    'owner_code',
                    'nearest_station',
                    'nearest_metro_station',
                    'site',
                    'email',
                    'address_comment',
                    'phones',
                    'type',
                    'have_cashless',
                    'have_cash',
                    'allowed_cod',
                    'is_dressing_room',
                    'is_handout',
                    'is_reception',
                    'weight_max',
                    'weight_min',
                ]
            ]));
    }*/

    public function test_checkout_request_returns_delivery_rules_validation_errors(): void
    {
        $this->createModels();
        $this->loginAsUser();
        /** @var Delivery $delivery */
        $delivery = Delivery::query()->where('driver', SdekCourier::KEY)->first();

        $data = $this->getRequestData();

        $this->postJson(route('api.v1.mobile.checkout.create-order', $data))
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'data' => __('validation.required', ['attribute' => __('cms-orders::site.checkout.Delivery')]),
            ]);

        $data['delivery_id'] = $delivery->id + 1;
        $this->postJson(route('api.v1.mobile.checkout.create-order', $data))
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'data' => __('validation.exists', ['attribute' => __('cms-orders::site.checkout.Delivery')]),
            ]);

        $data['delivery_id'] = $delivery->id;
        $this->postJson(route('api.v1.mobile.checkout.create-order', $data))
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'data' => __('validation.required', ['attribute' => __('cms-orders::site.checkout.Delivery data')]),
            ]);

        $data['delivery_data'] = [1];
        $this->postJson(route('api.v1.mobile.checkout.create-order', $data))
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'data' => __('validation.required', ['attribute' => __('cms-orders::site.checkout.Region')]),
            ]);

        $data['delivery_data']['region_code'] = 685;
        $this->postJson(route('api.v1.mobile.checkout.create-order', $data))
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'data' => __('validation.required', ['attribute' => __('cms-orders::site.checkout.Locality')]),
            ]);

        $data['delivery_data']['city_code'] = 4693;
        $this->postJson(route('api.v1.mobile.checkout.create-order', $data))
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'data' => __('validation.required', ['attribute' => __('cms-orders::site.checkout.Postal code')]),
            ]);

        $data['delivery_data']['postal_code'] = '141220';
        $data['delivery_data']['address'] = 'Test street, 25';
        $this->postJson(route('api.v1.mobile.checkout.create-order', $data))
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'data' => __('validation.required', ['attribute' => __('cms-orders::site.checkout.Tariff')]),
            ]);
    }

    private function getRequestData(): array
    {
        /** @var Payment $payment */
        $payment = Payment::factory()->create();

        return [
            'payment_id' => $payment->id,
            'recipient' => ['recipient_is_me' => true],
        ];
    }

    public function test_it_stores_delivery_data_on_creating_order(): void
    {
        $this->createModels();
        $this->loginAsUser();
        /** @var Delivery $delivery */
        $delivery = Delivery::query()->where('driver', SdekCourier::KEY)->first();

        $data = $this->getRequestData();
        $data['delivery_id'] = $delivery->id;
        $data['delivery_data'] = [
            'region_code' => 685,
            'city_code' => 4693,
            'postal_code' => '141220',
            'address' => 'Test street, 25',
            'tariff_code' => 139,
        ];

        /** @var DatabaseStorage $cart */
        $cart = app(CartInterface::class);
        $cart->getMainCart()->save();
        /** @var Provider $provider */
        $provider = Provider::factory()->create([
            'status' => ProviderStatus::MODERATED,
            'active' => true,
            'admin_id' => Administrator::factory()->create([ 'super_admin' => false ])->id,
        ]);
        /** @var Product $product */
        $product = Product::factory()->create([ 'cost' => 500, 'provider_id' => $provider->admin_id, ]);
        $cart->add($product, 2);

        Event::fake(CreatedOrders::class);

        $res = $this->postJson(route('api.v1.mobile.checkout.create-order', $data));

        $orderId = $res->json('data.orders.0.id');

        $order = Order::query()->find($orderId);

        self::assertEquals(
            $data['delivery_data'],
            $order->deliveryInformation->only(['region_code', 'city_code', 'postal_code', 'address', 'tariff_code'])
        );
    }

//    public function test_it_returns_tariffs_list(): void
//    {
//        $address = [
//            'city_code' => 4756,
//            'postal_code' => '050025',
//        ];
//
//        /** @var DatabaseStorage $cart */
//        $cart = app(CartInterface::class);
//        $cart->getMainCart()->save();
//
//        $admin1 = Administrator::factory()->create([ 'super_admin' => false ]);
//        $admin2 = Administrator::factory()->create([ 'super_admin' => false ]);
//
//        Provider::factory()->create([ 'admin_id' => $admin1 ]);
//        Provider::factory()->create([ 'admin_id' => $admin2 ]);
//
//        /** @var Product $product1 */
//        $product1 = Product::factory()->create([ 'provider_id' => $admin1->id ]);
//        /** @var Product $product2 */
//        $product2 = Product::factory()->create([ 'provider_id' => $admin2->id ]);
//        /** @var Product $product3 */
//        $product3 = Product::factory()->create([ 'provider_id' => $admin1->id ]);
//
//        $cart->add($product1, 1);
//        $cart->add($product2, 3);
//        $cart->add($product3, 1);
//
//        $res = $this->postJson(route('api.v1.mobile.sdek.tariffs', $address));
//
//        $res->assertJsonStructure($this->structureResource([
//            "*" => [
//                'tariff_name',
//                'tariff_code',
//                'delivery_sum',
//                'period_min',
//                'period_max',
//            ]
//        ]));
//
//        self::assertNotEmpty($cart->getMainCart()->delivery_data);
//
//        foreach ($res->json('data') as $tariffData) {
//            self::assertEquals(
//                $tariffData['delivery_sum'],
//                array_sum($cart->getMainCart()->delivery_data[$tariffData['tariff_code']])
//            );
//        }
//    }
//
//    public function test_it_returns_tariffs_list_for_given_case(): void
//    {
//        $address = [
//            'city_code' => 4756,
//            'postal_code' => '050025',
//        ];
//
//        /** @var DatabaseStorage $cart */
//        $cart = app(CartInterface::class);
//        $cart->getMainCart()->save();
//
//        /** @var Administrator $admin */
//        $admin = Administrator::factory()->create([ 'super_admin' => false ]);
//
//        Provider::factory()->create([
//            'admin_id' => $admin->id,
//            'region_code' => 299,
//            'city_code' => 4756,
//        ]);
//
//        /** @var Product $product */
//        $product = Product::factory()->create([
//            'provider_id' => $admin->id,
//            'weight' => 750,
//            'dimensions' => [10, 10, 10],
//        ]);
//
//        $cart->add($product, 1);
//
//        $res = $this->postJson(route('api.v1.mobile.sdek.tariffs', $address));
//
//        $res->assertJsonStructure($this->structureResource([
//            "*" => [
//                'tariff_name',
//                'tariff_code',
//                'delivery_sum',
//                'period_min',
//                'period_max',
//            ]
//        ]));
//
//        self::assertNotEmpty($cart->getMainCart()->delivery_data);
//
//        foreach ($res->json('data') as $tariffData) {
//            self::assertEquals(
//                $tariffData['delivery_sum'],
//                array_sum($cart->getMainCart()->delivery_data[$tariffData['tariff_code']])
//            );
//        }
//    }
}



