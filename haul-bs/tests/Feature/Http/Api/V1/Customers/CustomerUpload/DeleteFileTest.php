<?php

namespace Tests\Feature\Http\Api\V1\Customers\CustomerUpload;

use App\Models\Customers\Customer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\TestCase;

class DeleteFileTest extends TestCase
{
    use DatabaseTransactions;

    protected CustomerBuilder $customerBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->customerBuilder = resolve(CustomerBuilder::class);
    }

    /** @test */
    public function success_delete()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $file = UploadedFile::fake()->createWithContent('info.txt', 'Some text for file');

        /** @var $model Customer */
        $model = $this->customerBuilder->attachments($file)->create();

        $this->assertNotEmpty($model->getAttachments());

        $this->deleteJson(route('api.v1.customers.delete-file', [
            'id' => $model->id,
            'attachmentId' => $model->getAttachments()[0]->id
        ]))
            ->assertNoContent()
        ;

        $model->refresh();
        $this->assertEmpty($model->getAttachments());
    }

    /** @test */
    public function not_perm()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsMechanic();

        $file = UploadedFile::fake()->createWithContent('info.txt', 'Some text for file');

        /** @var $model Customer */
        $model = $this->customerBuilder->attachments($file)->create();

        $res = $this->deleteJson(route('api.v1.customers.delete-file', [
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

        /** @var $model Customer */
        $model = $this->customerBuilder->attachments($file)->create();

        $res = $this->deleteJson(route('api.v1.customers.delete-file', [
            'id' => $model->id,
            'attachmentId' => $model->getAttachments()[0]->id
        ]));

        self::assertUnauthenticatedMessage($res);
    }
}
