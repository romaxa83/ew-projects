<?php

namespace Tests\Feature\Mutations\User\User;

use App\Services\Media\UploadService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Queries\User\AuthUserTest;
use Tests\TestCase;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class UploadUserTest extends TestCase
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
    public function upload_passport_success_and_look()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $response = $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($images: [Upload]) {userUploadImage(type: '. $this->user_image_type_passport .', images: $images) {id, basename, type, hash, position, mime, url}}"}',
                'map' => '{ "images": ["variables.images"] }',
                'images' => [
                    UploadedFile::fake()->image('file.jpg', 1980, 1240)->size(500),
                    UploadedFile::fake()->image('file.jpg', 1980, 1240)->size(500),
                ]
            ], ['content-type' => 'multipart/form-data']
        );

        $responseData = $response->json('data.userUploadImage');

        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('basename', $responseData[0]);
        $this->assertArrayHasKey('type', $responseData[0]);
        $this->assertArrayHasKey('hash', $responseData[0]);
        $this->assertArrayHasKey('position', $responseData[0]);
        $this->assertArrayHasKey('mime', $responseData[0]);
        $this->assertArrayHasKey('url', $responseData[0]);
        $this->assertCount(2, $responseData);

        app(UploadService::class)->removeAllImageAtModel($user);
    }

    /** @test */
    public function upload_nothing()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $response = $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($images: [Upload]) {userUploadImage(type: '. $this->user_image_type_passport .', images: $images) {id, basename, type, hash, position, mime, url}}"}',
                'map' => '{ "images": ["variables.images"] }',
                'images' => []
            ], ['content-type' => 'multipart/form-data']
        );

        $responseData = $response->json('data.userUploadImage');

        $this->assertEmpty($responseData);

        app(UploadService::class)->removeAllImageAtModel($user);
    }

    /** @test */
    public function upload_different_type_and_look_profile()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        // сначал грузим фото по паспорту
        $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($images: [Upload]) {userUploadImage(type: '. $this->user_image_type_passport .', images: $images) {id, basename, type, hash, position, mime, url}}"}',
                'map' => '{ "images": ["variables.images"] }',
                'images' => [
                    UploadedFile::fake()->image('file.jpg', 1980, 1240)->size(500),
                    UploadedFile::fake()->image('file.jpg', 1980, 1240)->size(500),
                ]
            ], ['content-type' => 'multipart/form-data']
        );

        $response = $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($images: [Upload]) {userUploadImage(type: '. $this->user_image_type_avatar .', images: $images) {id, basename, type, hash, position, mime, url}}"}',
                'map' => '{ "images": ["variables.images"] }',
                'images' => [
                    UploadedFile::fake()->image('file.jpg', 1980, 1240)->size(500),
                ]
            ], ['content-type' => 'multipart/form-data']
        );

        $responseData = $response->json('data.userUploadImage');

        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('type', $responseData[0]);
        $this->assertCount(1, $responseData);
        $this->assertEquals( 'avatar', $responseData[0]['type']);

        $user->refresh();
        $this->assertCount(3, $user->images);

        app(UploadService::class)->removeAllImageAtModel($user);
    }
}
