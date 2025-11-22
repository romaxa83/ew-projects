<?php

namespace Tests\Feature\Http\Api\V1\Users\UserProfile;

use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class UploadTest extends TestCase
{
    use DatabaseTransactions;

    protected UserBuilder $userBuilder;

    protected $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success_upload()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        /** @var $model User */
        $model = $this->userBuilder->asSuperAdmin()->create();

        $this->loginUserAsSuperAdmin($model);

        $file = UploadedFile::fake()->image('avatar.png');

        $this->assertNull($model->getFirstImage());

        $this->postJson(route('api.v1.users.profile.upload-photo'), [
            'photo' => $file
        ])
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                ],
            ])
            ->assertJsonStructure([
                'data' => [
                    'photo' => [
                        'id',
                        'original',
                        'original_jpg',
                        'xs',
                        'xs_jpg',
                        'sm',
                        'sm_jpg',
                        'md',
                        'md_jpg',
                    ]
                ]
            ])
        ;

        $model->refresh();

        $this->assertNotNull($model->getFirstImage());
    }

    /** @test */
    public function success_reupload()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        /** @var $model User */
        $model = $this->userBuilder->asSuperAdmin()->create();

        $this->loginUserAsSuperAdmin($model);

        $file = UploadedFile::fake()->image('avatar.png');

        $this->postJson(route('api.v1.users.profile.upload-photo'), [
            'photo' => $file
        ]);

        $model->refresh();

        $id = $model->getFirstImage()->id;

        $file_2 = UploadedFile::fake()->image('avatar_new.png');

        $this->postJson(route('api.v1.users.profile.upload-photo'), [
            'photo' => $file_2
        ]);

        $model->refresh();

        $this->assertNotEquals($model->getFirstImage()->id, $id);
    }

    /** @test */
    public function not_auth()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $file = UploadedFile::fake()->image('avatar.png');

        $res = $this->postJson(route('api.v1.users.profile.upload-photo'), [
            'photo' => $file
        ]);

        self::assertUnauthenticatedMessage($res);
    }
}
