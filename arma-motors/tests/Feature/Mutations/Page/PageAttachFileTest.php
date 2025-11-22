<?php

namespace Tests\Feature\Mutations\Page;

use App\DTO\Media\FileDTO;
use App\Exceptions\ErrorsCode;
use App\Models\Media\File;
use App\Models\Page\Page;
use App\Services\Media\UploadService;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\OrderBuilder;
use Tests\Traits\Statuses;

class PageAttachFileTest extends TestCase
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
    public function success_attach_file()
    {
        $adminBuilder = $this->adminBuilder();
        $admin = $adminBuilder
            ->createRoleWithPerm(Permissions::PAGE_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = Page::where('id', 1)->first();

        $this->assertNull($model->file);

        $response = $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($file: [Upload]) {pageAttachFile(id: ' . $model->id .' file: $file) {id, basename, url, type, hash, mime, url}}"}',
                'map' => '{ "file": ["variables.file"] }',
                'file' => [
                    UploadedFile::fake()->image('file.pdf')->size(500),
                ]
            ], ['content-type' => 'multipart/form-data']
        );

        $responseData = $response->json('data.pageAttachFile');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('basename', $responseData);
        $this->assertArrayHasKey('url', $responseData);
        $this->assertArrayHasKey('type', $responseData);
        $this->assertArrayHasKey('hash', $responseData);

        $this->assertEquals($responseData['type'], Page::FILE_PDF_TYPE);

        $model->refresh();
        $this->assertNotNull($model->file);
        $this->assertEquals($model->file->id, $responseData['id']);

        app(UploadService::class)->removeAllFileAtModel($model);
    }

    /** @test */
    public function attach_file_if_exist_file()
    {
        $adminBuilder = $this->adminBuilder();
        $admin = $adminBuilder
            ->createRoleWithPerm(Permissions::PAGE_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = Page::where('id', 1)->first();

        $data = [
            'model' => File::MODEL_PAGE,
            'modelId' => $model->id,
            'type' => Page::FILE_PDF_TYPE,
            'file' => [
                UploadedFile::fake()->image('first.jpg', 1980, 1240)->size(500),
            ],
        ];
        $service = app(UploadService::class);
        $dto = FileDTO::byArgs($data);
        $service->uploadFile($dto);

        $model->refresh();

        $this->assertNotNull($model->file);

        $response = $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($file: [Upload]) {pageAttachFile(id: ' . $model->id .' file: $file) {id, basename, url, type, hash, mime, url}}"}',
                'map' => '{ "file": ["variables.file"] }',
                'file' => [
                    UploadedFile::fake()->image('file.pdf')->size(500),
                ]
            ], ['content-type' => 'multipart/form-data']
        );

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.page have file'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::BAD_REQUEST, $response->json('errors.0.extensions.code'));

        app(UploadService::class)->removeAllFileAtModel($model);
    }

    /** @test */
    public function not_auth()
    {
        $adminBuilder = $this->adminBuilder();
        $admin = $adminBuilder
            ->createRoleWithPerm(Permissions::PAGE_EDIT)
            ->create();

        $model = Page::where('id', 1)->first();

        $response = $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($file: [Upload]) {pageAttachFile(id: ' . $model->id .' file: $file) {id, basename, url, type, hash, mime, url}}"}',
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

        $model = Page::where('id', 1)->first();

        $response = $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($file: [Upload]) {pageAttachFile(id: ' . $model->id .' file: $file) {id, basename, url, type, hash, mime, url}}"}',
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
}

