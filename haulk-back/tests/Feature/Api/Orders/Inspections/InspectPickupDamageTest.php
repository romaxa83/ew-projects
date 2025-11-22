<?php

namespace Tests\Feature\Api\Orders\Inspections;

use App\Models\Files\File;
use App\Models\Orders\Inspection;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Tests\Feature\Api\Orders\OrderTestCase;
use Tests\Helpers\Traits\OrderFactoryHelper;

class InspectPickupDamageTest extends OrderTestCase
{
    use OrderFactoryHelper;
    use DatabaseTransactions;

    public function test_it_upload_damage_photo_success(): void
    {
        Config::set('orders.inspection.max_photo', 1);

        $this->loginAsCarrierDriver();

        $pickupInspection = Inspection::factory()->create(
            [
                'has_vin_inspection' => true,
            ]
        );

        $deliveryInspection = Inspection::factory()->create();
        $image = UploadedFile::fake()->image('image1.jpeg');

        $deliveryInspection->addPhoto(1, $image);

        $order = $this->orderFactory(
            [
                'driver_id' => $this->authenticatedUser->id,
                'pickup_inspection_id' => $pickupInspection->id,
                'delivery_inspection_id' => $deliveryInspection->id,
            ]
        );
        $vehicle = $order->vehicles->first();

        $this->assertDatabaseMissing(
            File::TABLE_NAME,
            [
                'model_id' => $pickupInspection->id,
                'model_type' => Inspection::class,
                'collection_name' => Order::INSPECTION_DAMAGE_COLLECTION_NAME,
            ]
        );

        $this->postJson(
            route('mobile.orders.vehicles.inspect-pickup-damage', [$order->id, $vehicle->id]),
            [
                Order::INSPECTION_DAMAGE_FIELD_NAME => UploadedFile::fake()->image('some_name.jpg'),
            ]
        )
            ->assertOk();

        $this->assertDatabaseHas(
            File::TABLE_NAME,
            [
                'model_id' => $pickupInspection->id,
                'model_type' => Inspection::class,
                'collection_name' => Order::INSPECTION_DAMAGE_COLLECTION_NAME,
            ]
        );
    }

    public function test_store_pickup_damage_labels(): void
    {
        Config::set('orders.inspection.max_photo', 1);

        $labels = [
            'MD',
            'FT',
            'S',
        ];

        $this->loginAsCarrierDriver();

        $pickupInspection = Inspection::factory()->create(
            [
                'has_vin_inspection' => true,
            ]
        );

        $deliveryInspection = Inspection::factory()->create();
        $image = UploadedFile::fake()->image('image1.jpeg');

        $deliveryInspection->addPhoto(1, $image);

        $order = $this->orderFactory(
            [
                'driver_id' => $this->authenticatedUser->id,
                'pickup_inspection_id' => $pickupInspection->id,
                'delivery_inspection_id' => $deliveryInspection->id,
            ]
        );
        $vehicle = $order->vehicles->first();

        $this->assertDatabaseMissing(
            File::TABLE_NAME,
            [
                'model_id' => $pickupInspection->id,
                'model_type' => Inspection::class,
                'collection_name' => Order::INSPECTION_DAMAGE_COLLECTION_NAME,
            ]
        );

        $this->postJson(
            route('mobile.orders.vehicles.inspect-pickup-damage', [$order->id, $vehicle->id]),
            [
                Order::INSPECTION_DAMAGE_FIELD_NAME => UploadedFile::fake()->image('some_name.jpg'),
                'damage_labels' => $labels,
            ]
        )
            ->assertOk();

        $this->assertDatabaseHas(
            File::TABLE_NAME,
            [
                'model_id' => $pickupInspection->id,
                'model_type' => Inspection::class,
                'collection_name' => Order::INSPECTION_DAMAGE_COLLECTION_NAME,
            ]
        );

        $vehicle->refresh();

        $this->assertEquals($labels, $vehicle->pickupInspection->damage_labels);
    }
}
