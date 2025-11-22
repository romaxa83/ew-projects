<?php

namespace Tests\Feature\Api\Orders\Inspections;

use App\Models\Orders\Inspection;
use App\Models\Orders\Order;
use App\Services\Images\DrawingImageInterface;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\Fake\Services\Images\ImageDrawingFakeService;
use Tests\Feature\Api\Orders\OrderTestCase;
use Tests\Helpers\Traits\OrderFactoryHelper;

class InspectPickupExteriorTest extends OrderTestCase
{
    use DatabaseTransactions;
    use OrderFactoryHelper;

    public function test_it_load_inspection_photo_success(): void
    {
        $fakeImageDrawingService = new ImageDrawingFakeService();

        $this->app->bind(
            DrawingImageInterface::class,
            fn() => $fakeImageDrawingService
        );

        $this->loginAsCarrierDriver();

        $inspection = Inspection::factory()->create(
            [
                'has_vin_inspection' => true,
            ]
        );
        $order = $this->orderFactory(
            [
                'driver_id' => $this->authenticatedUser->id,
                'pickup_inspection_id' => $inspection->id,
            ]
        );
        $vehicle = $order->vehicles->first();
        $timestamp = now()->subDays(2)->timestamp;

        $this->postJson(
            route('mobile.orders.vehicles.inspect-pickup-exterior', [$order->id, $vehicle->id]),
            [
                'photo_id' => 1,
                Order::INSPECTION_PHOTO_FIELD_NAME => UploadedFile::fake()->image('some_name.jpg'),
                'photo_lat' => 50.12345,
                'photo_lng' => 60.98765,
                'photo_timestamp' => $timestamp,
            ]
        )
            ->assertOk()
            ->assertJsonPath('data.pickup_inspection.photos.inspection_photo_1.created_at', $timestamp);

        self::assertNotNull($fakeImageDrawingService->getLine());
    }
}
