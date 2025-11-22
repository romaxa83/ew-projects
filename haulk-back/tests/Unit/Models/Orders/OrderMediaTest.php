<?php

namespace Tests\Unit\Models\Orders;

use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig;
use Tests\TestCase;

class OrderMediaTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function test_it_get_media_and_search_by_part_name()
    {
        $image1 = UploadedFile::fake()->image('image1.jpeg');
        $image2 = UploadedFile::fake()->image('image2.jpeg');
        $image3 = UploadedFile::fake()->image('image3.jpeg');
        $image4 = UploadedFile::fake()->image('image4.jpeg');
        $image5 = UploadedFile::fake()->image('image5.jpeg');
        $image6 = UploadedFile::fake()->image('image6.jpeg');

        $order = Order::factory()->create();

        $order->addMediaWithRandomName(Order::INSPECTION_PHOTO_COLLECTION_NAME . '_1', $image1);
        $order->addMediaWithRandomName(Order::INSPECTION_PHOTO_COLLECTION_NAME . '_2', $image2);
        $order->addMediaWithRandomName(Order::INSPECTION_PHOTO_COLLECTION_NAME . '_3', $image3);
        $order->addMediaWithRandomName(Order::INSPECTION_PHOTO_COLLECTION_NAME . '_4', $image4);

        $order->addMediaWithRandomName(Order::INSPECTION_DAMAGE_COLLECTION_NAME . '_1', $image5);
        $order->addMediaWithRandomName(Order::INSPECTION_DAMAGE_COLLECTION_NAME . '_2', $image6);

        $media = $order->media;

        $this->assertCount(4, $media->whereLike('collection_name', Order::INSPECTION_PHOTO_COLLECTION_NAME));
    }
}
