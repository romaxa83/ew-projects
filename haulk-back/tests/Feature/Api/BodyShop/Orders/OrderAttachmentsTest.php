<?php

namespace Api\BodyShop\Orders;

use App\Models\BodyShop\Orders\Order;
use App\Models\Files\File;
use App\Services\BodyShop\Orders\OrderService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig;
use Tests\TestCase;

class OrderAttachmentsTest extends TestCase
{
    use DatabaseTransactions;

    private OrderService $service;

    public function test_it_attach_to_order_new_file()
    {
        $this->loginAsBodyShopSuperAdmin();

        $order = factory(Order::class)->create();

        $attributes = [
            'attachment' => UploadedFile::fake()->createWithContent('info.txt', 'Some text for file'),
        ];
        $this->postJson(route('body-shop.orders.attachments', $order), $attributes)
            ->assertOk();

        $this->assertDatabaseHas(
            File::TABLE_NAME,
            [
                'model_type' => Order::class,
                'model_id' => $order->id,
            ]
        );
    }

    public function test_it_has_error_for_unauthorized_attach_order()
    {
        $order = factory(Order::class)->create();

        $attributes = [
            'attachment' => UploadedFile::fake()->createWithContent('info.txt', 'Some text for file'),
        ];
        $this->postJson(route('body-shop.orders.attachments', $order), $attributes)
            ->assertUnauthorized();
    }

    /**
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function test_it_delete_attachment_file_success()
    {
        $order = factory(Order::class)->create();

        $attachment = UploadedFile::fake()->createWithContent('info.txt', 'Some text for file');

        $this->service->addAttachment($order, $attachment);

        $attachments = $order->getAttachments();
        $attachment = array_shift($attachments);

        $this->assertDatabaseHas(
            File::TABLE_NAME,
            [
                'id' => $attachment->id
            ]
        );

        $this->loginAsBodyShopSuperAdmin();

        $this->deleteJson(route('body-shop.orders.delete-attachments', ['order' => $order->id, 'id' => $attachment->id]))
            ->assertNoContent();

        $this->assertDatabaseMissing(
            File::TABLE_NAME,
            [
                'id' => $attachment->id
            ]
        );
    }

    /**
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function test_it_has_unauthorized_error_for_not_logged_deleter()
    {
        $order = factory(Order::class)->create();

        $attachment = UploadedFile::fake()->createWithContent('info.txt', 'Some text for file');

        $this->service->addAttachment($order, $attachment);

        $attachments = $order->getAttachments();
        $attachment = array_shift($attachments);
        $this->deleteJson(route('body-shop.orders.delete-attachments', ['order' => $order->id, 'id' => $attachment->id]))
            ->assertUnauthorized();
    }

    public function test_add_attachement_to_finished_order()
    {
        $this->loginAsBodyShopSuperAdmin();

        $order = factory(Order::class)->create(['status' => Order::STATUS_FINISHED]);

        $attributes = [
            'attachment' => UploadedFile::fake()->createWithContent('info.txt', 'Some text for file'),
        ];
        $this->postJson(route('body-shop.orders.attachments', $order), $attributes)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_it_delete_attachment_for_finished_order()
    {
        $order = factory(Order::class)->create(['status' => Order::STATUS_FINISHED]);

        $attachment = UploadedFile::fake()->createWithContent('info.txt', 'Some text for file');

        $this->service->addAttachment($order, $attachment);

        $attachments = $order->getAttachments();
        $attachment = array_shift($attachments);


        $this->loginAsBodyShopSuperAdmin();

        $this->deleteJson(route('body-shop.orders.delete-attachments', ['order' => $order->id, 'id' => $attachment->id]))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = resolve(OrderService::class);
    }
}
