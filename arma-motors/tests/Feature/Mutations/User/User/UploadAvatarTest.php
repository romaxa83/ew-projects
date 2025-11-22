<?php

namespace Tests\Feature\Mutations\User\User;

use App\Models\Admin\Admin;
use App\Models\User\User;
use App\Services\Media\UploadService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Mutations\Media\RemoveImageTest;
use Tests\Feature\Queries\Admin\AuthAdminTest;
use Tests\Feature\Queries\User\AuthUserTest;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class UploadAvatarTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use Statuses;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success_upload()
    {
        $user = $this->userBuilder()->phoneVerify()->create();
        $this->loginAsUser($user);

        $user->refresh();
        $this->assertNull($user->avatar);

        $response = $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($images: [Upload]) {userUploadAvatar(images: $images) {id, basename, url, type, hash, position, sizes, mime, url}}"}',
                'map' => '{ "images": ["variables.images"] }',
                'images' => [
                    UploadedFile::fake()->image('file.jpg', 1980, 1240)->size(500),
                ]
            ], ['content-type' => 'multipart/form-data']
        );

        $responseData = $response->json('data.userUploadAvatar');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('basename', $responseData);
        $this->assertArrayHasKey('type', $responseData);
        $this->assertArrayHasKey('hash', $responseData);
        $this->assertArrayHasKey('position', $responseData);
        $this->assertArrayHasKey('sizes', $responseData);
        $this->assertArrayHasKey('url', $responseData);
        $this->assertArrayHasKey('mime', $responseData);

        $this->assertCount(3, $responseData['sizes']);
        $this->assertEquals($responseData['type'], User::IMAGE_AVATAR_TYPE);

        $user->refresh();
        $this->assertNotNull($user->avatar);
        $this->assertEquals($responseData['id'], $user->avatar->id);

        // проверяем профиль
        $responseAuthUser = $this->graphQL(AuthUserTest::getQueryStr());

        $responseDataAuthUser = $responseAuthUser->json('data.authUser');

        $this->assertEquals($user->id, $responseDataAuthUser['id']);
        $this->assertArrayHasKey('avatar', $responseDataAuthUser);
        $this->assertArrayHasKey('id', $responseDataAuthUser['avatar']);
        $this->assertArrayHasKey('url', $responseDataAuthUser['avatar']);
        $this->assertArrayHasKey('sizes', $responseDataAuthUser['avatar']);
        $this->assertCount(3, $responseDataAuthUser['avatar']['sizes']);
        $this->assertEquals($user->avatar->id, $responseDataAuthUser['avatar']['id']);

        app(UploadService::class)->removeAllImageAtModel($user);
    }

    /** @test */
    public function success_upload_and_remove_old()
    {
        $user = $this->userBuilder()->phoneVerify()->create();
        $this->loginAsUser($user);

        $user->refresh();
        $this->assertNull($user->avatar);

        $response = $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($images: [Upload]) {userUploadAvatar(images: $images) {id, basename, url, type, hash, position, sizes, mime, url}}"}',
                'map' => '{ "images": ["variables.images"] }',
                'images' => [
                    UploadedFile::fake()->image('file.jpg', 1980, 1240)->size(500),
                ]
            ], ['content-type' => 'multipart/form-data']
        );

        $user->refresh();
        $this->assertNotNull($user->avatar);

        $avatarId = $response->json('data.userUploadAvatar.id');

        $response = $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($images: [Upload]) {userUploadAvatar(images: $images) {id, basename, url, type, hash, position, sizes, mime, url}}"}',
                'map' => '{ "images": ["variables.images"] }',
                'images' => [
                    UploadedFile::fake()->image('file.jpg', 1980, 1240)->size(500),
                ]
            ], ['content-type' => 'multipart/form-data']
        );

        $this->assertNotEquals($avatarId, $response->json('data.userUploadAvatar.id'));
        $user->refresh();

        $this->assertEquals($user->avatar->id, $response->json('data.userUploadAvatar.id'));
        $this->assertCount(1, $user->images);

        app(UploadService::class)->removeAllImageAtModel($user);
    }

    /** @test */
    public function success_remove()
    {
        $user = $this->userBuilder()->phoneVerify()->create();
        $this->loginAsUser($user);

        $user->refresh();
        $this->assertNull($user->avatar);

        // запрос на добавление
        $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($images: [Upload]) {userUploadAvatar(images: $images) {id, basename, url, type, hash, position, sizes, mime, url}}"}',
                'map' => '{ "images": ["variables.images"] }',
                'images' => [
                    UploadedFile::fake()->image('file.jpg', 1980, 1240)->size(500),
                ]
            ], ['content-type' => 'multipart/form-data']
        );
        $user->refresh();
        $this->assertNotNull($user->avatar);

        // запрос на удаление
        $re = $this->graphQL(RemoveImageTest::getQueryStrOneModel($user->avatar->id));

        $user->refresh();
        $this->assertNull($user->avatar);
    }
}


