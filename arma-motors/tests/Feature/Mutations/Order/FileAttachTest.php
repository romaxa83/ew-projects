<?php

namespace Tests\Feature\Mutations\Order;

use App\DTO\Media\FileDTO;
use App\Exceptions\ErrorsCode;
use App\Models\Media\File;
use App\Models\Order\Order;
use App\Services\Media\UploadService;
use App\Types\Order\Status;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\OrderBuilder;
use Tests\Traits\Statuses;

class FileAttachTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use OrderBuilder;
    use Statuses;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success_attach_bill()
    {
        $adminBuilder = $this->adminBuilder();
        $admin = $adminBuilder
            ->createRoleWithPerm(Permissions::ORDER_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder->setStatus(Status::DRAFT)->asOne()->create();

        $this->assertNull($order->billFile);

        $response = $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($file: [Upload]) {orderAttachFile(id: ' . $order->id .' type:'. Order::FILE_BILL_TYPE .' file: $file) {id, basename, url, type, hash, mime, url}}"}',
                'map' => '{ "file": ["variables.file"] }',
                'file' => [
                    UploadedFile::fake()->image('file.pdf')->size(500),
                ]
            ], ['content-type' => 'multipart/form-data']
        );

        $responseData = $response->json('data.orderAttachFile');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('basename', $responseData);
        $this->assertArrayHasKey('url', $responseData);
        $this->assertArrayHasKey('type', $responseData);
        $this->assertArrayHasKey('hash', $responseData);

        $this->assertEquals($responseData['type'], Order::FILE_BILL_TYPE);

        $order->refresh();
        $this->assertNotNull($order->billFile);
        $this->assertEquals($order->billFile->id, $responseData['id']);

        app(UploadService::class)->removeAllFileAtModel($order);
    }

    /** @test */
    public function attach_bill_if_exist_file()
    {
        $adminBuilder = $this->adminBuilder();
        $admin = $adminBuilder
            ->createRoleWithPerm(Permissions::ORDER_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder->setStatus(Status::DRAFT)->asOne()->create();

        $data = [
            'model' => File::MODEL_ORDER,
            'modelId' => $order->id,
            'type' => Order::FILE_BILL_TYPE,
            'file' => [
                UploadedFile::fake()->image('first.jpg', 1980, 1240)->size(500),
            ],
        ];
        $service = app(UploadService::class);
        $dto = FileDTO::byArgs($data);
        $service->uploadFile($dto);

        $order->refresh();

        $this->assertNotNull($order->billFile);

        $response = $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($file: [Upload]) {orderAttachFile(id: ' . $order->id .' type:'. Order::FILE_BILL_TYPE .' file: $file) {id, basename, url, type, hash, mime, url}}"}',
                'map' => '{ "file": ["variables.file"] }',
                'file' => [
                    UploadedFile::fake()->image('file.pdf')->size(500),
                ]
            ], ['content-type' => 'multipart/form-data']
        );

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.order.order have file', ['type' => Order::FILE_BILL_TYPE]), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::BAD_REQUEST, $response->json('errors.0.extensions.code'));

        app(UploadService::class)->removeAllFileAtModel($order);
    }

    /** @test */
    public function success_attach_act()
    {
        $adminBuilder = $this->adminBuilder();
        $admin = $adminBuilder
            ->createRoleWithPerm(Permissions::ORDER_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder->setStatus(Status::DRAFT)->asOne()->create();

        $this->assertNull($order->actFile);

        $response = $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($file: [Upload]) {orderAttachFile(id: ' . $order->id .' type:'. Order::FILE_ACT_TYPE .' file: $file) {id, basename, url, type, hash, mime, url}}"}',
                'map' => '{ "file": ["variables.file"] }',
                'file' => [
                    UploadedFile::fake()->image('file.pdf')->size(500),
                ]
            ], ['content-type' => 'multipart/form-data']
        );

        $responseData = $response->json('data.orderAttachFile');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('basename', $responseData);
        $this->assertArrayHasKey('url', $responseData);
        $this->assertArrayHasKey('type', $responseData);
        $this->assertArrayHasKey('hash', $responseData);

        $this->assertEquals($responseData['type'], Order::FILE_ACT_TYPE);

        $order->refresh();
        $this->assertNotNull($order->actFile);
        $this->assertEquals($order->actFile->id, $responseData['id']);

        app(UploadService::class)->removeAllFileAtModel($order);
    }

    /** @test */
    public function attach_act_if_exist_file()
    {
        $adminBuilder = $this->adminBuilder();
        $admin = $adminBuilder
            ->createRoleWithPerm(Permissions::ORDER_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder->setStatus(Status::DRAFT)->asOne()->create();

        $data = [
            'model' => File::MODEL_ORDER,
            'modelId' => $order->id,
            'type' => Order::FILE_ACT_TYPE,
            'file' => [
                UploadedFile::fake()->image('first.jpg', 1980, 1240)->size(500),
            ],
        ];
        $service = app(UploadService::class);
        $dto = FileDTO::byArgs($data);
        $service->uploadFile($dto);

        $order->refresh();

        $this->assertNotNull($order->actFile);

        $response = $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($file: [Upload]) {orderAttachFile(id: ' . $order->id .' type:'. Order::FILE_ACT_TYPE .' file: $file) {id, basename, url, type, hash, mime, url}}"}',
                'map' => '{ "file": ["variables.file"] }',
                'file' => [
                    UploadedFile::fake()->image('file.pdf')->size(500),
                ]
            ], ['content-type' => 'multipart/form-data']
        );

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.order.order have file', ['type' => Order::FILE_ACT_TYPE]), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::BAD_REQUEST, $response->json('errors.0.extensions.code'));

        app(UploadService::class)->removeAllFileAtModel($order);
    }

    /** @test */
    public function not_auth()
    {
        $adminBuilder = $this->adminBuilder();
        $admin = $adminBuilder
            ->createRoleWithPerm(Permissions::ORDER_EDIT)
            ->create();

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder->setStatus(Status::DRAFT)->asOne()->create();

        $this->assertNull($order->actFile);

        $response = $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($file: [Upload]) {orderAttachFile(id: ' . $order->id .' type:'. Order::FILE_ACT_TYPE .' file: $file) {id, basename, url, type, hash, mime, url}}"}',
                'map' => '{ "file": ["variables.file"] }',
                'file' => [
                    UploadedFile::fake()->image('file.pdf')->size(500),
                ]
            ], ['content-type' => 'multipart/form-data']
        );

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $adminBuilder = $this->adminBuilder();
        $admin = $adminBuilder
            ->createRoleWithPerm(Permissions::ORDER_LIST)
            ->create();
        $this->loginAsAdmin($admin);

        $orderBuilder = $this->orderBuilder();
        $order = $orderBuilder->setStatus(Status::DRAFT)->asOne()->create();

        $this->assertNull($order->actFile);

        $response = $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($file: [Upload]) {orderAttachFile(id: ' . $order->id .' type:'. Order::FILE_ACT_TYPE .' file: $file) {id, basename, url, type, hash, mime, url}}"}',
                'map' => '{ "file": ["variables.file"] }',
                'file' => [
                    UploadedFile::fake()->image('file.pdf')->size(500),
                ]
            ], ['content-type' => 'multipart/form-data']
        );

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                orderEdit(input: {
                    id: "%s"
                    adminId: %s
                    status: %s
                }) {
                    id
                    status
                    admin {
                        id
                    }
                }
            }',
            $data['id'],
            $data['adminId'],
            $data['status'],
        );
    }
}

