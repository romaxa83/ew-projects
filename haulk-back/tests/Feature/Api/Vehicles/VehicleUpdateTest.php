<?php

namespace Tests\Feature\Api\Vehicles;

use App\Models\Users\User;
use App\Models\Vehicles\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

abstract class VehicleUpdateTest extends TestCase
{
    use DatabaseTransactions;

    protected string $routeName = '';

    protected string $tableName = '';

    abstract protected function getVehicle(array $attributes = []): Vehicle;

    abstract protected function getRequestData(): array;

    abstract protected function loginAsPermittedUser(): User;

    abstract protected function loginAsNotPermittedUser(): User;

    protected function getComparingDBData(): array
    {
        return $this->getRequestData();
    }

    public function test_it_not_update_for_unauthorized_users(): void
    {
        $this->postJson(route($this->routeName, $this->getVehicle()))
            ->assertUnauthorized();
    }

    public function test_it_update(): void
    {
        $this->assertDatabaseMissing($this->tableName, $this->getComparingDBData());

        $this->loginAsPermittedUser();

        $this->postJson(route($this->routeName, $this->getVehicle()), $this->getRequestData())
            ->assertOk();

        $this->assertDatabaseHas($this->tableName, $this->getComparingDBData());
    }

    public function test_it_update_with_attachments(): void
    {
        $this->assertDatabaseMissing($this->tableName, $this->getComparingDBData());

        $this->loginAsPermittedUser();

        $attachments = [
            Vehicle::ATTACHMENT_FIELD_NAME => [
                UploadedFile::fake()->image('image1.jpg'),
                UploadedFile::fake()->image('image2.png'),
            ],
        ];

        $vehicle = $this->getVehicle();

        $data = $this->postJson(route($this->routeName, $vehicle), $this->getRequestData() + $attachments)
            ->assertOk();

        $this->assertDatabaseHas($this->tableName, $this->getComparingDBData());
        $this->assertCount(2, $data['data'][Vehicle::ATTACHMENT_COLLECTION_NAME]);
    }
}
