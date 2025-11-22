<?php

namespace Tests\Feature\Api\Vehicles;

use App\Models\Files\File;
use App\Models\Users\User;
use App\Models\Vehicles\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

abstract class VehicleDeleteAttachmentTest extends TestCase
{
    use DatabaseTransactions;

    protected string $routeName = '';

    abstract protected function getVehicle(array $attributes = []): Vehicle;

    abstract protected function loginAsPermittedUser(): User;

    abstract protected function loginAsNotPermittedUser(): User;

    public function test_it_not_delete_for_unauthorized_users(): void
    {
        $vehicle = $this->getVehicle();
        $attachment = UploadedFile::fake()->image('info.png');
        $vehicle->addMediaWithRandomName(Vehicle::ATTACHMENT_COLLECTION_NAME, $attachment);

        $attachments = $vehicle->getAttachments();
        $attachment = array_shift($attachments);

        $this->assertDatabaseHas(
            File::TABLE_NAME,
            [
                'id' => $attachment->id
            ]
        );

        $this->deleteJson(route($this->routeName, [$vehicle->id, $attachment->id]))
            ->assertUnauthorized();
    }

    public function test_it_not_delete_for_not_permitted_users(): void
    {
        $vehicle = $this->getVehicle();
        $attachment = UploadedFile::fake()->image('info.png');
        $vehicle->addMediaWithRandomName(Vehicle::ATTACHMENT_COLLECTION_NAME, $attachment);

        $attachments = $vehicle->getAttachments();
        $attachment = array_shift($attachments);

        $this->assertDatabaseHas(
            File::TABLE_NAME,
            [
                'id' => $attachment->id
            ]
        );

        $this->loginAsNotPermittedUser();

        $this->deleteJson(route($this->routeName, [$vehicle->id, $attachment->id]))
            ->assertForbidden();
    }

    public function test_it_delete(): void
    {
        $vehicle = $this->getVehicle();
        $attachment = UploadedFile::fake()->image('info.png');
        $vehicle->addMediaWithRandomName(Vehicle::ATTACHMENT_COLLECTION_NAME, $attachment);

        $attachments = $vehicle->getAttachments();
        $attachment = array_shift($attachments);

        $this->assertDatabaseHas(
            File::TABLE_NAME,
            [
                'id' => $attachment->id
            ]
        );

        $this->loginAsPermittedUser();
        $this->deleteJson(route($this->routeName, [$vehicle->id, $attachment->id]))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing(
            File::TABLE_NAME,
            [
                'id' => $attachment->id
            ]
        );
    }
}
