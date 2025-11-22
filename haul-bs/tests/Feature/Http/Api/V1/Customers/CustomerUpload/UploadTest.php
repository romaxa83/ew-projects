<?php

namespace Tests\Feature\Http\Api\V1\Customers\CustomerUpload;

use App\Models\Customers\Customer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\TestCase;

class UploadTest extends TestCase
{
    use DatabaseTransactions;

    protected CustomerBuilder $customerBuilder;

    protected $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->customerBuilder = resolve(CustomerBuilder::class);

        $this->data = [
            'attachment' => UploadedFile::fake()->createWithContent('info.txt', 'Some text for file'),
        ];
    }

    /** @test */
    public function success_upload()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        /** @var $model Customer */
        $model = $this->customerBuilder->create();

        $data = $this->data;

        $this->assertEmpty($model->getAttachments());

        $this->postJson(route('api.v1.customers.upload-file', ['id' => $model->id]), $data)
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
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.customers.upload-file', ['id' => 0]), $data)
        ;

        self::assertErrorMsg($res, __("exceptions.customer.not_found"), Response::HTTP_NOT_FOUND);
    }


    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Customer */
        $model = $this->customerBuilder->create();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.customers.upload-file', ['id' => $model->id]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Customer */
        $model = $this->customerBuilder->create();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.customers.upload-file', ['id' => $model->id]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
