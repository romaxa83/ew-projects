<?php

namespace Tests\Feature\Api\Orders;

use App\Models\Orders\Inspection;
use App\Models\Orders\Order;
use App\Models\Tags\Tag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\TestCase;

class OrderShowTest extends TestCase
{
    use DatabaseTransactions;

    use OrderFactoryHelper;

    /**
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function test_order_inspection_has_media()
    {
        $image1 = UploadedFile::fake()->image('image1.jpeg');
        $image2 = UploadedFile::fake()->image('image2.jpeg');
        $image3 = UploadedFile::fake()->image('image3.jpeg');
        $image4 = UploadedFile::fake()->image('image4.jpeg');

        $pickupInspection = Inspection::factory()->create();
        $deliveryInspection = Inspection::factory()->create();

        $pickupInspection->addMediaWithRandomName(Order::INSPECTION_PHOTO_COLLECTION_NAME . '_1', $image1);
        $pickupInspection->addMediaWithRandomName(Order::INSPECTION_PHOTO_COLLECTION_NAME . '_2', $image2);
        $pickupInspection->addMediaWithRandomName(Order::INSPECTION_DAMAGE_COLLECTION_NAME . '_1', $image3);
        $pickupInspection->addMediaWithRandomName(Order::INSPECTION_DAMAGE_COLLECTION_NAME . '_2', $image4);

        $order = $this->orderFactory(
            [
                'pickup_inspection_id' => $pickupInspection->id,
                'delivery_inspection_id' => $deliveryInspection->id
            ]
        );

        $this->loginAsCarrierAdmin();

        $response = $this->getJson(route('orders.show', $order))
            ->assertOk();

        $photos = $response->json('data.vehicles.0.pickup_inspection.photos');

        $this->assertCount(2, $photos);
    }

    public function test_if_order_media_is_not_exists_then_photos_is_null()
    {
        $pickupInspection = Inspection::factory()->create();
        $deliveryInspection = Inspection::factory()->create();

        $order = $this->orderFactory(
            [
                'pickup_inspection_id' => $pickupInspection->id,
                'delivery_inspection_id' => $deliveryInspection->id
            ]
        );

        $this->loginAsCarrierAdmin();

        $response = $this->getJson(route('orders.show', $order))
            ->assertOk();

        $photos = $response->json('data.vehicles.0.pickup_inspection.photos');

        $this->assertIsArray($photos);
    }

    public function test_order_with_tags()
    {
        $order = $this->orderFactory();

        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $order->tags()->sync([$tag1->id, $tag2->id]);

        $this->loginAsCarrierAdmin();

        $response = $this->getJson(route('orders.show', $order))
            ->assertOk();

        $tags = $response->json('data.tags');
        $this->assertCount(2, $tags);
    }
}
