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

class UploadTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;

    protected $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->orderBuilder = resolve(OrderBuilder::class);

        $this->data = [
            'attachment' => UploadedFile::fake()->createWithContent('info.txt', 'Some text for file'),
        ];
    }

    /** @test */
    public function success_upload()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $user = $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data = $this->data;

        $this->assertEmpty($model->getAttachments());

        $this->postJson(route('api.v1.orders.bs.upload-file', ['id' => $model->id]), $data)
            ->assertJsonStructure([
                'data' => [
                    'attachments' => [
                        [
                            'id',
                            'name',
                            'file_name',
                            'mime_type',
                            'url',
                            'size',
                            'created_at',
                        ]
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                ],
            ])
            ->assertJsonCount(1, 'data.attachments')
        ;

        $model->refresh();
        $history = $model->histories[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.order.bs.attached_document');

        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'email' => $user->email->getValue(),
            'full_name' => $user->full_name,
            'user_id' => $user->id,
            'order_number' => $model->order_number,
            'order_id' => $model->id,
        ]);

        $media = $model->getAttachments()[0];

        $this->assertEquals($history->details, [
            "attachments.{$media->id}.name" => [
                'old' => null,
                'new' => $media->name,
                'type' => 'added',
            ],
        ]);
    }

    /** @test */
    public function success_upload_add_more()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $file = UploadedFile::fake()->create('info_1.txt');

        /** @var $model Order */
        $model = $this->orderBuilder->attachments($file)->create();

        $data = $this->data;

        $this->assertCount(1, $model->getAttachments());

        $this->postJson(route('api.v1.orders.bs.upload-file', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                ],
            ])
            ->assertJsonCount(2, 'data.attachments')
        ;
    }

    /** @test */
    public function fail_order_is_finished()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->status(OrderStatus::Finished->value)->create();

        $data = $this->data;

        $this->assertEmpty($model->getAttachments());

        $res = $this->postJson(route('api.v1.orders.bs.upload-file', ['id' => $model->id]), $data)
        ;

        self::assertErrorMsg($res, __("exceptions.orders.bs.finished_order_cant_be_edited"), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_not_found()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.orders.bs.upload-file', ['id' => 0]), $data)
        ;

        self::assertErrorMsg($res, __("exceptions.orders.bs.not_found"), Response::HTTP_NOT_FOUND);
    }


    /** @test */
    public function not_perm()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsMechanic();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.orders.bs.upload-file', ['id' => $model->id]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.orders.bs.upload-file', ['id' => $model->id]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
