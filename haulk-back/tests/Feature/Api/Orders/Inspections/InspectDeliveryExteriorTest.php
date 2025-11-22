<?php

namespace Tests\Feature\Api\Orders\Inspections;

use App\Models\Orders\Inspection;
use App\Models\Orders\Order;
use App\Services\Images\DrawingImageInterface;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig;
use Tests\Fake\Services\Images\ImageDrawingFakeService;
use Tests\Feature\Api\Orders\OrderTestCase;
use Tests\Helpers\Traits\OrderFactoryHelper;

class InspectDeliveryExteriorTest extends OrderTestCase
{
    use DatabaseTransactions;
    use OrderFactoryHelper;

    private ImageDrawingFakeService $fakeImageDrawingService;

    public function test_it_load_delivery_inspection_photo_success(): void
    {
        $this->loginAsCarrierDriver();

        $pickupInspection = Inspection::factory()->create(
            [
                'has_vin_inspection' => true,
            ]
        );

        $deliveryInspection = Inspection::factory()->create();

        $order = $this->orderFactory(
            [
                'driver_id' => $this->authenticatedUser->id,
                'pickup_inspection_id' => $pickupInspection->id,
                'delivery_inspection_id' => $deliveryInspection->id,
            ]
        );
        $vehicle = $order->vehicles->first();

        $this->postJson(
            route('mobile.orders.vehicles.inspect-delivery-exterior', [$order->id, $vehicle->id]),
            [
                'photo_id' => 1,
                Order::INSPECTION_PHOTO_FIELD_NAME => UploadedFile::fake()->image('some_name.jpg'),
                'photo_lat' => 50.12345,
                'photo_lng' => 60.98765,
            ]
        )
            ->assertOk();

        self::assertNotNull($this->fakeImageDrawingService->getLine());
    }

    /**
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function test_it_has_validate_error_for_photo_id_field(): void
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

        $response = $this->postJson(
            route('mobile.orders.vehicles.inspect-delivery-exterior', [$order->id, $vehicle->id]),
            [
                'photo_id' => 2,
                Order::INSPECTION_PHOTO_FIELD_NAME => UploadedFile::fake()->image('some_name.jpg'),
                'photo_lat' => 50.12345,
                'photo_lng' => 60.98765,
            ]
        )
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        self::assertEquals('Photo inspection limit - 1', $response->json('errors.0.title'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->fakeImageDrawingService = new ImageDrawingFakeService();

        $this->app->bind(
            DrawingImageInterface::class,
            function () {
                return $this->fakeImageDrawingService;
            }
        );
    }
}
