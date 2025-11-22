<?php

namespace Tests\Feature\Api\Vehicles;

use App\Models\Users\User;
use App\Models\Vehicles\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

abstract class VehicleShowTest extends TestCase
{
    use DatabaseTransactions;

    protected string $routeName = '';

    abstract protected function getVehicle(array $attributes = []): Vehicle;

    abstract protected function getResponseFields(): array;

    abstract protected function loginAsPermittedUser(): User;

    abstract protected function loginAsNotPermittedUser(): User;

    public function test_it_not_show_for_unauthorized_users(): void
    {
        $this->getJson(route($this->routeName, $this->getVehicle()))
            ->assertUnauthorized();
    }

    public function test_it_not_show_for_not_permitted_users(): void
    {
        $this->loginAsNotPermittedUser();

        $this->getJson(route($this->routeName, $this->getVehicle()))
            ->assertForbidden();
    }

    public function test_it_show_for_permitted_users(): void
    {
        $vehicle = $this->getVehicle();

        $this->loginAsPermittedUser();

        $this->getJson(route($this->routeName, $vehicle))
            ->assertOk()
            ->assertJsonStructure(['data' => $this->getResponseFields()]);
    }
}
