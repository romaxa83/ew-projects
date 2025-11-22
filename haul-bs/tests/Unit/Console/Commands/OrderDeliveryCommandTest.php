<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\Orders\OrderDeliveryStatusUpdate;
use App\Enums\Orders\Parts\DeliveryMethod;
use App\Enums\Orders\Parts\DeliveryStatus;
use App\Enums\Orders\Parts\OrderStatus;
use App\Models\Orders\Parts\Delivery;
use App\Models\Orders\Parts\Order;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OrderDeliveryCommandTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.ups.sandbox', true);
        Config::set('services.fedex.sandbox', true);
    }

    private function httpFakeFedex(): void
    {
        Http::fake([
            'apis-sandbox.fedex.com/oauth/token' => Http::response(['access_token' => 'test']),
        ]);
    }

    private function httpFakeUps(): void
    {
        Http::fake([
            'wwwcie.ups.com/security/v1/oauth/token' => Http::response(['access_token' => 'test']),
        ]);
    }

    private function httpFakeUsps(): void
    {
        Http::fake([
            'api.usps.com/oauth2/v3/token' => Http::response(['access_token' => 'test']),
        ]);
    }

    public function test_change_status_fedex(): void
    {
        $this->httpFakeFedex();

        Http::fake([
            'apis-sandbox.fedex.com/track/v1/trackingnumbers' => Http::response(['output' => [
                'completeTrackResults' => [
                    [
                        'trackResults' => [
                            [
                                'latestStatusDetail' => [
                                    'statusByLocale' => 'Delivered'
                                ]
                            ]
                        ]
                    ]
                ]
            ]])
        ]);

        $order = Order::factory()->create([
            'status' => OrderStatus::Sent
        ]);
        $delivery = Delivery::factory()->for($order)->create([
            'method' => DeliveryMethod::Fedex,
            'status' => DeliveryStatus::Sent,
            'tracking_number' => '122816215025810',
        ]);

        $this->artisan(OrderDeliveryStatusUpdate::class)
            ->assertExitCode(Command::SUCCESS);

        $this->assertDatabaseHas(Order::TABLE, [
            'id' => $order->id,
            'status' => OrderStatus::Delivered,
        ]);

        $this->assertDatabaseHas(Delivery::TABLE, [
            'id' => $delivery->id,
            'status' => DeliveryStatus::Delivered,
        ]);
    }

    public function test_change_status_fedex_not_delivery(): void
    {
        $this->httpFakeFedex();

        Http::fake([
            'apis-sandbox.fedex.com/track/v1/trackingnumbers' => Http::response(['output' => [
                'completeTrackResults' => [
                    [
                        'trackResults' => [
                            [
                                'latestStatusDetail' => [
                                    'statusByLocale' => 'Delivery exception'
                                ]
                            ]
                        ]
                    ]
                ]
            ]])
        ]);

        $order = Order::factory()->create([
            'status' => OrderStatus::Sent
        ]);
        $delivery = Delivery::factory()->for($order)->create([
            'method' => DeliveryMethod::Fedex,
            'status' => DeliveryStatus::Sent,
            'tracking_number' => '076288115212522',
        ]);

        $this->artisan(OrderDeliveryStatusUpdate::class)
            ->assertExitCode(Command::SUCCESS);

        $this->assertDatabaseHas(Order::TABLE, [
            'id' => $order->id,
            'status' => OrderStatus::Sent,
        ]);

        $this->assertDatabaseHas(Delivery::TABLE, [
            'id' => $delivery->id,
            'status' => DeliveryStatus::Sent,
        ]);
    }

    public function test_change_status_ups(): void
    {
        $this->httpFakeUps();

        Http::fake([
            'wwwcie.ups.com/api/track/v1/details/*' => Http::response(['trackResponse' => [
                'shipment' => [
                    [
                        'package' => [
                            [
                                'currentStatus' => [
                                    'code' => '040'
                                ]
                            ]
                        ]
                    ]
                ]
            ]]),
        ]);

        $order = Order::factory()->create([
            'status' => OrderStatus::Sent
        ]);
        $delivery = Delivery::factory()->for($order)->create([
            'method' => DeliveryMethod::UPS,
            'status' => DeliveryStatus::Sent,
            'tracking_number' => '1Z1202R66698804005',
        ]);

        $this->artisan(OrderDeliveryStatusUpdate::class)
            ->assertExitCode(Command::SUCCESS);

        $this->assertDatabaseHas(Order::TABLE, [
            'id' => $order->id,
            'status' => OrderStatus::Delivered,
        ]);

        $this->assertDatabaseHas(Delivery::TABLE, [
            'id' => $delivery->id,
            'status' => DeliveryStatus::Delivered,
        ]);
    }
    public function test_change_status_ups_not_delivery(): void
    {
        $this->httpFakeUps();

        Http::fake([
            'wwwcie.ups.com/api/track/v1/details/*' => Http::response(['trackResponse' => [
                'shipment' => [
                    [
                        'package' => [
                            [
                                'currentStatus' => [
                                    'code' => '050'
                                ]
                            ]
                        ]
                    ]
                ]
            ]]),
        ]);

        $order = Order::factory()->create([
            'status' => OrderStatus::Sent
        ]);
        $delivery = Delivery::factory()->for($order)->create([
            'method' => DeliveryMethod::UPS,
            'status' => DeliveryStatus::Sent,
            'tracking_number' => '1Z1202R66698804005',
        ]);

        $this->artisan(OrderDeliveryStatusUpdate::class)
            ->assertExitCode(Command::SUCCESS);

        $this->assertDatabaseHas(Order::TABLE, [
            'id' => $order->id,
            'status' => OrderStatus::Sent,
        ]);

        $this->assertDatabaseHas(Delivery::TABLE, [
            'id' => $delivery->id,
            'status' => DeliveryStatus::Sent,
        ]);
    }

    public function test_change_status_usps(): void
    {
        $this->httpFakeUsps();

        Http::fake([
            'api.usps.com/tracking/v3/tracking/*' => Http::response(['statusCategory' => 'Delivered']),
        ]);

        $order = Order::factory()->create([
            'status' => OrderStatus::Sent
        ]);
        $delivery = Delivery::factory()->for($order)->create([
            'method' => DeliveryMethod::USPS,
            'status' => DeliveryStatus::Sent,
            'tracking_number' => '9234690113176726268223',
        ]);

        $this->artisan(OrderDeliveryStatusUpdate::class)
            ->assertExitCode(Command::SUCCESS);

        $this->assertDatabaseHas(Order::TABLE, [
            'id' => $order->id,
            'status' => OrderStatus::Delivered,
        ]);

        $this->assertDatabaseHas(Delivery::TABLE, [
            'id' => $delivery->id,
            'status' => DeliveryStatus::Delivered,
        ]);
    }
    public function test_change_status_usps_not_delivery(): void
    {
        $this->httpFakeUsps();

        Http::fake([
            'api.usps.com/tracking/v3/tracking/*' => Http::response(['statusCategory' => 'Not Delivered']),
        ]);


        $order = Order::factory()->create([
            'status' => OrderStatus::Sent
        ]);
        $delivery = Delivery::factory()->for($order)->create([
            'method' => DeliveryMethod::USPS,
            'status' => DeliveryStatus::Sent,
            'tracking_number' => '1Z1202R66698804005',
        ]);

        $this->artisan(OrderDeliveryStatusUpdate::class)
            ->assertExitCode(Command::SUCCESS);

        $this->assertDatabaseHas(Order::TABLE, [
            'id' => $order->id,
            'status' => OrderStatus::Sent,
        ]);

        $this->assertDatabaseHas(Delivery::TABLE, [
            'id' => $delivery->id,
            'status' => DeliveryStatus::Sent,
        ]);
    }
}
