<?php

namespace Api\BodyShop\Settings;

use App\Models\BodyShop\Settings\Settings;
use App\Models\Files\File;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig;
use Tests\TestCase;

class SettingsLogoTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_attach_logo()
    {
        $this->loginAsBodyShopSuperAdmin();

        $attributes = [
            'logo' => UploadedFile::fake()->image('logo.png'),
        ];

        $this->assertDatabaseMissing(
            File::TABLE_NAME,
            [
                'model_type' => Settings::class,
            ],
        );

        $this->postJson(route('body-shop.settings.upload-info-photo'), $attributes)
            ->assertOk();

        $this->assertDatabaseHas(
            File::TABLE_NAME,
            [
                'model_type' => Settings::class,
            ],
        );
    }

    /**
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function test_it_delete_logo()
    {
        $this->loginAsBodyShopSuperAdmin();

        $attributes = [
            'logo' => UploadedFile::fake()->image('logo.png'),
        ];

        $this->assertDatabaseMissing(
            File::TABLE_NAME,
            [
                'model_type' => Settings::class,
            ],
        );

        $this->postJson(route('body-shop.settings.upload-info-photo'), $attributes)
            ->assertOk();

        $this->assertDatabaseHas(
            File::TABLE_NAME,
            [
                'model_type' => Settings::class,
            ]
        );

        $this->deleteJson(route('body-shop.settings.delete-info-photo'))
            ->assertNoContent();

        $this->assertDatabaseMissing(
            File::TABLE_NAME,
            [
                'model_type' => Settings::class,
            ]
        );
    }

}
