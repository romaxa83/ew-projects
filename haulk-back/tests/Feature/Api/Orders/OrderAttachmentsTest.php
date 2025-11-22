<?php

namespace Tests\Feature\Api\Orders;

use App\Models\Files\File;
use App\Models\Orders\Order;
use App\Models\Users\User;
use App\Services\Orders\OrderService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\TestCase;

class OrderAttachmentsTest extends TestCase
{
    use DatabaseTransactions;
    use OrderFactoryHelper;

    private OrderService $service;

    public function test_it_attach_to_user_new_file(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $order = $this->orderFactory();

        $attributes = [
            'attachment' => UploadedFile::fake()->createWithContent('info.txt', 'Some text for user file'),
        ];
        $response = $this->postJson(route('orders.attachments', $order), $attributes)
            ->assertOk();

        $orderResource = json_to_array($response->content())['data'];

        self::assertCount(1, $orderResource[Order::ATTACHMENT_COLLECTION_NAME]);
    }

    public function test_it_has_error_for_unauthorized_attach_user(): void
    {
        $order = $this->dispatcherFactory();

        $attributes = [
            'attachment' => UploadedFile::fake()->createWithContent('info.txt', 'Some text for user file'),
        ];
        $this->postJson(route('orders.attachments', $order), $attributes)
            ->assertUnauthorized();
    }

    public function test_it_has_forbidden_for_not_permitted_user(): void
    {
        $this->loginAsCarrierAccountant();

        $order = $this->orderFactory();

        $attributes = [
            'attachment' => UploadedFile::fake()->createWithContent('info.txt', 'Some text for user file'),
        ];

        $this->postJson(route('orders.attachments', $order), $attributes)
            ->assertForbidden();
    }

    /**
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function test_it_delete_attachment_file_success(): void
    {
        $order = $this->orderFactory();

        $user = $this->loginAsCarrierSuperAdmin();

        $this->service->setUser($user);

        $attachment = UploadedFile::fake()->createWithContent('info.txt', 'Some text for user file');

        $this->service->addAttachment($order, $attachment);

        $attachments = $order->getAttachments();
        $attachment = array_shift($attachments);

        $this->assertDatabaseHas(
            File::TABLE_NAME,
            [
                'id' => $attachment->id
            ]
        );

        $this->deleteJson(route('orders.delete-attachments', ['order' => $order->id, 'id' => $attachment->id]))
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
    public function test_it_has_unauthorized_error_for_not_logged_deleter(): void
    {
        $order = $this->orderFactory();

        $user = User::factory()->create();
        $user->assignRole(User::SUPERADMIN_ROLE);

        $this->service->setUser($user);

        $attachment = UploadedFile::fake()->createWithContent('info.txt', 'Some text for user file');

        $this->service->addAttachment($order, $attachment);

        $attachments = $order->getAttachments();
        $attachment = array_shift($attachments);

        $this->deleteJson(
            route(
                'orders.delete-attachments',
                ['order' => $order->id, 'id' => $attachment->id]
            )
        )
            ->assertUnauthorized();
    }

    /**
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function test_it_has_forbidden_error_for_not_permitted_deleter(): void
    {
        $user = $this->loginAsCarrierAccountant();

        $this->service->setUser($user);

        $order = $this->orderFactory();

        $attachment = UploadedFile::fake()->createWithContent('info.txt', 'Some text for user file');

        $this->service->addAttachment($order, $attachment);

        $attachments = $order->getAttachments();
        $attachment = array_shift($attachments);
        $this->deleteJson(route('orders.delete-attachments', ['order' => $order->id, 'id' => $attachment->id]))
            ->assertForbidden();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = resolve(OrderService::class);
    }
}
