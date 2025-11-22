<?php

namespace Api\BodyShop\VehicleOwners;

use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\Files\File;
use App\Services\BodyShop\VehicleOwners\VehicleOwnerService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig;
use Tests\TestCase;

class VehicleOwnerAttachmentsTest extends TestCase
{
    use DatabaseTransactions;

    private VehicleOwnerService $service;

    public function test_it_attach_to_vehicle_owner_new_file()
    {
        $this->loginAsBodyShopSuperAdmin();

        $vehicleOwner = factory(VehicleOwner::class)->create();

        $attributes = [
            'attachment' => UploadedFile::fake()->createWithContent('info.txt', 'Some text for file'),
        ];
        $this->postJson(route('body-shop.vehicle-owners.attachments', $vehicleOwner), $attributes)
            ->assertOk();

        $this->assertDatabaseHas(
            File::TABLE_NAME,
            [
                'model_type' => VehicleOwner::class,
                'model_id' => $vehicleOwner->id,
            ]
        );
    }

    public function test_it_has_error_for_unauthorized_attach_vehicle_owner()
    {
        $vehicleOwner = factory(VehicleOwner::class)->create();

        $attributes = [
            'attachment' => UploadedFile::fake()->createWithContent('info.txt', 'Some text for file'),
        ];
        $this->postJson(route('body-shop.vehicle-owners.attachments', $vehicleOwner), $attributes)
            ->assertUnauthorized();
    }

    /**
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function test_it_delete_attachment_file_success()
    {
        $vehicleOwner = factory(VehicleOwner::class)->create();

        $attachment = UploadedFile::fake()->createWithContent('info.txt', 'Some text for file');

        $this->service->addAttachment($vehicleOwner, $attachment);

        $attachments = $vehicleOwner->getAttachments();
        $attachment = array_shift($attachments);

        $this->assertDatabaseHas(
            File::TABLE_NAME,
            [
                'id' => $attachment->id
            ]
        );

        $this->loginAsBodyShopSuperAdmin();

        $this->deleteJson(route('body-shop.vehicle-owners.delete-attachments', ['vehicleOwner' => $vehicleOwner->id, 'id' => $attachment->id]))
            ->assertNoContent();

        $this->assertDatabaseMissing(
            File::TABLE_NAME,
            [
                'id' => $attachment->id
            ]
        );
    }

    /**
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function test_it_has_unauthorized_error_for_not_logged_deleter()
    {
        $vehicleOwner = factory(VehicleOwner::class)->create();

        $attachment = UploadedFile::fake()->createWithContent('info.txt', 'Some text for file');

        $this->service->addAttachment($vehicleOwner, $attachment);

        $attachments = $vehicleOwner->getAttachments();
        $attachment = array_shift($attachments);
        $this->deleteJson(route('body-shop.vehicle-owners.delete-attachments', ['vehicleOwner' => $vehicleOwner->id, 'id' => $attachment->id]))
            ->assertUnauthorized();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = resolve(VehicleOwnerService::class);
    }
}
