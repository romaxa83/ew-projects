<?php

namespace Feature\Http\Api\V1\Settings\SettingUpload;

use App\Events\Events\Settings\RequestToEcom;
use App\Events\Listeners\Settings\RequestToEcomListener;
use App\Models\Settings\Settings;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeleteEcomTest extends TestCase
{
    use DatabaseTransactions;

    protected $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->data = [
            'logo' => UploadedFile::fake()->image('logo.png'),
        ];
    }

    /** @test */
    public function success_delete()
    {
        Event::fake([RequestToEcom::class]);

        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $this->assertFalse(Settings::query()->where('name', Settings::ECOMM_LOGO_FIELD)->exists());

        // upload
        $this->postJson(route('api.v1.settings.upload-ecommerce-logo'), $data);

        $model = Settings::query()->where('name', Settings::ECOMM_LOGO_FIELD)->first();

        $this->assertNotNull($model);
        $this->assertNotNull($model->getFirstMedia(Settings::ECOMM_LOGO_FIELD));

        // delete
        $this->deleteJson(route('api.v1.settings.delete-ecommerce-logo'));

        $model->refresh();

        $this->assertNull($model->getFirstMedia(Settings::ECOMM_LOGO_FIELD));

        Event::assertDispatched(RequestToEcom::class);
        Event::assertListening(RequestToEcom::class, RequestToEcomListener::class);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $res = $this->deleteJson(route('api.v1.settings.delete-ecommerce-logo'));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->deleteJson(route('api.v1.settings.delete-ecommerce-logo'));

        self::assertUnauthenticatedMessage($res);
    }
}
