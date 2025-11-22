<?php

namespace Tests\Feature\Http\Api\V1\Orders\BS\Upload;

use App\Enums\Orders\BS\OrderStatus;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Models\Orders\BS\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Orders\BS\OrderBuilder;
use Tests\TestCase;

class DeleteFileTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->orderBuilder = resolve(OrderBuilder::class);
    }

    /** @test */
    public function success_delete()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $user = $this->loginUserAsSuperAdmin();

        $file = UploadedFile::fake()->createWithContent('info.txt', 'Some text for file');

        /** @var $model Order */
        $model = $this->orderBuilder->attachments($file)->create();

        $this->assertNotEmpty($model->getAttachments());

        $old = clone $model->getAttachments()[0];

        $this->deleteJson(route('api.v1.orders.bs.delete-file', [
            'id' => $model->id,
            'attachmentId' => $model->getAttachments()[0]->id
        ]))
            ->assertNoContent()
        ;

        $model->refresh();
        $this->assertEmpty($model->getAttachments());

        $model->refresh();

        $history = $model->histories[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.media.deleted');

        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'email' => $user->email->getValue(),
            'full_name' => $user->full_name,
            'user_id' => $user->id,
            'order_number' => $model->order_number,
            'order_id' => $model->id,
        ]);

        $this->assertEquals($history->details, [
            "attachments.{$old->id}.name" => [
                'new' => null,
                'old' => $old->name,
                'type' => 'removed',
            ],
        ]);
    }

    /** @test */
    public function fail_order_is_finished()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $file = UploadedFile::fake()->createWithContent('info.txt', 'Some text for file');

        /** @var $model Order */
        $model = $this->orderBuilder->attachments($file)->status(OrderStatus::Finished->value)->create();

        $this->assertNotEmpty($model->getAttachments());

        $res = $this->deleteJson(route('api.v1.orders.bs.delete-file', [
            'id' => $model->id,
            'attachmentId' => $model->getAttachments()[0]->id
        ]))
        ;

        self::assertErrorMsg($res, __("exceptions.orders.bs.finished_order_cant_be_edited"), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function not_perm()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsMechanic();

        $file = UploadedFile::fake()->createWithContent('info.txt', 'Some text for file');

        /** @var $model Order */
        $model = $this->orderBuilder->attachments($file)->create();

        $res = $this->deleteJson(route('api.v1.orders.bs.delete-file', [
            'id' => $model->id,
            'attachmentId' => $model->getAttachments()[0]->id
        ]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $file = UploadedFile::fake()->createWithContent('info.txt', 'Some text for file');

        /** @var $model Order */
        $model = $this->orderBuilder->attachments($file)->create();

        $res = $this->deleteJson(route('api.v1.orders.bs.delete-file', [
            'id' => $model->id,
            'attachmentId' => $model->getAttachments()[0]->id
        ]));

        self::assertUnauthenticatedMessage($res);
    }
}
