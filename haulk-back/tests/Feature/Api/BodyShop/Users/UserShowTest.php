<?php

namespace Tests\Feature\Api\BodyShop\Users;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class UserShowTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_it_not_show_user_for_unauthorized_users(): void
    {
        $user = $this->bsAdminFactory();

        $this->getJson(route('body-shop.users.show', $user))
            ->assertUnauthorized();
    }

    public function test_it_not_show_user_for_not_permitted_users(): void
    {
        $user = $this->bsAdminFactory();

        $this->loginAsBodyShopMechanic();

        $this->getJson(route('body-shop.users.show', $user))
            ->assertForbidden();
    }

    public function test_it_not_show_user_from_company(): void
    {
        $user = $this->dispatcherFactory();

        $this->loginAsBodyShopSuperAdmin();

        $this->getJson(route('body-shop.users.show', $user))
            ->assertNotFound();
    }

    public function test_it_show_user_for_permitted_users(): void
    {
        $user = $this->bsAdminFactory();

        $this->loginAsBodyShopSuperAdmin();

        $this->getJson(route('body-shop.users.show', $user))
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        'id',
                        'first_name',
                        'last_name',
                        'full_name',
                        'email',
                        'phone',
                        'phones',
                    ]
                ]
            );
    }
}
