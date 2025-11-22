<?php

namespace Tests\Feature\Http\Api\V1\Users\UserProfile;

use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class DeletePhotoTest extends TestCase
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
    public function success_delete()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        /** @var $model User */
        $model = $this->userBuilder->asSuperAdmin()->create();

        $this->loginUserAsSuperAdmin($model);

        $file = UploadedFile::fake()->image('avatar.png');

        $this->assertNull($model->getFirstImage());

        // upload
        $this->postJson(route('api.v1.users.profile.upload-photo'), [
            'photo' => $file
        ]);

        $model->refresh();

        $this->assertNotNull($model->getFirstImage());

        // delete
        $this->deleteJson(route('api.v1.users.profile.delete-photo'))
            ->assertNoContent()
        ;

        $model->refresh();

        $this->assertNull($model->getFirstImage());
    }

    /** @test */
    public function success_delete_if_empty()
    {
        /** @var $model User */
        $model = $this->loginUserAsSuperAdmin();

        $this->assertNull($model->getFirstImage());

        // delete
        $this->deleteJson(route('api.v1.users.profile.delete-photo'))
            ->assertNoContent()
        ;

        $model->refresh();

        $this->assertNull($model->getFirstImage());

    }

    /** @test */
    public function not_auth()
    {
        $res = $this->deleteJson(route('api.v1.users.profile.delete-photo'));

        self::assertUnauthenticatedMessage($res);
    }
}
