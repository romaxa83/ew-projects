<?php

namespace Tests\Feature\Http\Api\V1\Vehicles\Truck\Action;

use App\Foundations\Modules\History\Enums\HistoryType;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\TestCase;

class DeleteAttachmentTest extends TestCase
{
    use DatabaseTransactions;

    protected TruckBuilder $truckBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->truckBuilder = resolve(TruckBuilder::class);
    }

    /** @test */
    public function success_delete()
    {
        $user = $this->loginUserAsSuperAdmin();

        $file = UploadedFile::fake()->createWithContent('info.pdf', 'Some text for file');

        /** @var $model Truck */
        $model = $this->truckBuilder->attachments($file)->create();

        $this->assertNotEmpty($model->getAttachments());

        $old = clone $model->getAttachments()[0];

        $this->deleteJson(route('api.v1.vehicles.trucks.delete-file', [
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
        $this->assertEquals($history->msg, 'history.vehicle.file_deleted');
        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'vehicle_type' => __('history.vehicle.truck'),
            'user_id' => $user->id,
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
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Truck */
        $model = $this->truckBuilder->create();

        $res = $this->deleteJson(route('api.v1.vehicles.trucks.delete-file', [
            'id' => $model->id,
            'attachmentId' => 0
        ]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Truck */
        $model = $this->truckBuilder->create();

        $res = $this->deleteJson(route('api.v1.vehicles.trucks.delete-file', [
            'id' => $model->id,
            'attachmentId' => 0
        ]));

        self::assertUnauthenticatedMessage($res);
    }
}
