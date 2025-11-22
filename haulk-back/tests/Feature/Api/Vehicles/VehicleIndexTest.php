<?php

namespace Tests\Feature\Api\Vehicles;

use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

abstract class VehicleIndexTest extends TestCase
{
    use DatabaseTransactions;

    protected string $routeName = '';

    abstract protected function loginAsPermittedUser(): User;

    abstract protected function loginAsNotPermittedUser(): User;

    public function test_it_forbidden_for_not_authorized_users(): void
    {
        $this->getJson(route($this->routeName))
            ->assertUnauthorized();
    }

    public function test_it_forbidden_for_permitted_users(): void
    {
        $this->loginAsNotPermittedUser();

        $this->getJson(route($this->routeName))
            ->assertForbidden();
    }

    public function test_it_show_all(): void
    {
        $this->loginAsPermittedUser();
        $this->getJson(route($this->routeName))
            ->assertOk();
    }
}
