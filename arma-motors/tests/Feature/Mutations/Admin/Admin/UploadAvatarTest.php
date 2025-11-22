<?php

namespace Tests\Feature\Mutations\Admin\Admin;

use App\Models\Admin\Admin;
use App\Services\Media\UploadService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Mutations\Media\RemoveImageTest;
use Tests\Feature\Queries\Admin\AuthAdminTest;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Statuses;

class UploadAvatarTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use Statuses;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success_upload()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $admin->refresh();
        $this->assertNull($admin->avatar);

        $response = $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($images: [Upload]) {adminUploadAvatar(images: $images) {id, basename, url, type, hash, position, sizes, mime, url}}"}',
                'map' => '{ "images": ["variables.images"] }',
                'images' => [
                    UploadedFile::fake()->image('file.jpg', 1980, 1240)->size(500),
                ]
            ], ['content-type' => 'multipart/form-data']
        );

        $responseData = $response->json('data.adminUploadAvatar');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('basename', $responseData);
        $this->assertArrayHasKey('type', $responseData);
        $this->assertArrayHasKey('hash', $responseData);
        $this->assertArrayHasKey('position', $responseData);
        $this->assertArrayHasKey('sizes', $responseData);
        $this->assertArrayHasKey('url', $responseData);
        $this->assertArrayHasKey('mime', $responseData);

        $this->assertCount(3, $responseData['sizes']);
        $this->assertEquals($responseData['type'], Admin::IMAGE_AVATAR_TYPE);

        $admin->refresh();
        $this->assertNotNull($admin->avatar);
        $this->assertEquals($responseData['id'], $admin->avatar->id);

        // проверяем профиль
        $responseAuthAdmin = $this->graphQL(AuthAdminTest::getQueryStr());

        $responseDataAuthAdmin = $responseAuthAdmin->json('data.authAdmin');

        $this->assertEquals($admin->id, $responseDataAuthAdmin['id']);
        $this->assertArrayHasKey('avatar', $responseDataAuthAdmin);
        $this->assertArrayHasKey('id', $responseDataAuthAdmin['avatar']);
        $this->assertArrayHasKey('url', $responseDataAuthAdmin['avatar']);
        $this->assertArrayHasKey('sizes', $responseDataAuthAdmin['avatar']);
        $this->assertCount(3, $responseDataAuthAdmin['avatar']['sizes']);
        $this->assertEquals($admin->avatar->id, $responseDataAuthAdmin['avatar']['id']);

        app(UploadService::class)->removeAllImageAtModel($admin);
    }

    /** @test */
    public function success_upload_and_remove_old()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $admin->refresh();
        $this->assertNull($admin->avatar);

        $response = $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($images: [Upload]) {adminUploadAvatar(images: $images) {id, basename, url, type, hash, position, sizes, mime, url}}"}',
                'map' => '{ "images": ["variables.images"] }',
                'images' => [
                    UploadedFile::fake()->image('file.jpg', 1980, 1240)->size(500),
                ]
            ], ['content-type' => 'multipart/form-data']
        );

        $admin->refresh();
        $this->assertNotNull($admin->avatar);

        $avatarId = $response->json('data.adminUploadAvatar.id');

        $response = $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($images: [Upload]) {adminUploadAvatar(images: $images) {id, basename, url, type, hash, position, sizes, mime, url}}"}',
                'map' => '{ "images": ["variables.images"] }',
                'images' => [
                    UploadedFile::fake()->image('file.jpg', 1980, 1240)->size(500),
                ]
            ], ['content-type' => 'multipart/form-data']
        );

        $this->assertNotEquals($avatarId, $response->json('data.adminUploadAvatar.id'));
        $admin->refresh();

        $this->assertEquals($admin->avatar->id, $response->json('data.adminUploadAvatar.id'));
        $this->assertCount(1, $admin->images);

        app(UploadService::class)->removeAllImageAtModel($admin);
    }

    /** @test */
    public function success_remove()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $admin->refresh();
        $this->assertNull($admin->avatar);

        // запрос на добавление
        $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($images: [Upload]) {adminUploadAvatar(images: $images) {id, basename, url, type, hash, position, sizes, mime, url}}"}',
                'map' => '{ "images": ["variables.images"] }',
                'images' => [
                    UploadedFile::fake()->image('file.jpg', 1980, 1240)->size(500),
                ]
            ], ['content-type' => 'multipart/form-data']
        );
        $admin->refresh();
        $this->assertNotNull($admin->avatar);

        // запрос на удаление
        $re = $this->graphQL(RemoveImageTest::getQueryStrOneModel($admin->avatar->id));

        $admin->refresh();
        $this->assertNull($admin->avatar);
    }
}


