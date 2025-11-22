<?php

namespace Tests\Feature\Http\Api\V1\Vehicles\Trailer\Action;

use App\Foundations\Modules\History\Enums\HistoryType;
use App\Models\Vehicles\Trailer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\Builders\Vehicles\TrailerBuilder;
use Tests\TestCase;

class DeleteAttachmentTest extends TestCase
{
    use DatabaseTransactions;

    protected TrailerBuilder $trailerBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->trailerBuilder = resolve(TrailerBuilder::class);
    }

    /** @test */
    public function success_delete()
    {
        $user = $this->loginUserAsSuperAdmin();

        $file = UploadedFile::fake()->createWithContent('info.pdf', 'Some text for file');

        /** @var $model Trailer */
        $model = $this->trailerBuilder->attachments($file)->create();

        $this->assertNotEmpty($model->getAttachments());

        $old = clone $model->getAttachments()[0];

        $this->deleteJson(route('api.v1.vehicles.trailers.delete-file', [
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
            'vehicle_type' => __('history.vehicle.trailer'),
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

        /** @var $model Trailer */
        $model = $this->trailerBuilder->create();

        $res = $this->deleteJson(route('api.v1.vehicles.trailers.delete-file', [
            'id' => $model->id,
            'attachmentId' => 0
        ]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Trailer */
        $model = $this->trailerBuilder->create();

        $res = $this->deleteJson(route('api.v1.vehicles.trailers.delete-file', [
            'id' => $model->id,
            'attachmentId' => 0
        ]));

        self::assertUnauthenticatedMessage($res);
    }
}
