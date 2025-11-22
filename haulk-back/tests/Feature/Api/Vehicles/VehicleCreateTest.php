<?php

namespace Tests\Feature\Api\Vehicles;

use App\Models\Users\User;
use App\Models\Vehicles\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

abstract class VehicleCreateTest extends TestCase
{
    use DatabaseTransactions;

    protected string $routeName = '';

    protected string $tableName = '';

    abstract protected function getRequestData(): array;

    abstract protected function loginAsPermittedUser(): User;

    protected function getComparingDBData(): array
    {
        return $this->getRequestData();
    }

    public function test_it_forbidden_to_users_create_for_not_authorized_users(): void
    {
        $this->postJson(route($this->routeName), [])->assertUnauthorized();
    }

    public function test_it_create(): void
    {
        $this->loginAsPermittedUser();

        $this->assertDatabaseMissing($this->tableName, $this->getComparingDBData());

        $this->postJson(route($this->routeName), $this->getRequestData())
            ->assertCreated();

        $this->assertDatabaseHas($this->tableName, $this->getComparingDBData());
    }

    public function test_it_create_with_files(): void
    {
        $this->loginAsPermittedUser();

        $this->assertDatabaseMissing($this->tableName, $this->getComparingDBData());

        $attachments = [
            Vehicle::ATTACHMENT_FIELD_NAME => [
                UploadedFile::fake()->image('image1.jpg'),
                UploadedFile::fake()->image('image2.png'),
            ],
        ];

        $data = $this->postJson(route($this->routeName), $this->getRequestData() + $attachments)
            ->assertCreated();

        $this->assertDatabaseHas($this->tableName, $this->getComparingDBData());
        $this->assertCount(2, $data['data'][Vehicle::ATTACHMENT_COLLECTION_NAME]);
    }

    /**
     * @param $attributes
     * @param $expectErrors
     * @dataProvider formSubmitDataProvider
     */
    public function test_it_validation_messages($attributes, $expectErrors): void
    {
        $this->loginAsPermittedUser();

        $this->postJson(route($this->routeName), $attributes)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonFragment($expectErrors);
    }

    abstract public function formSubmitDataProvider(): array;
}
