<?php

namespace Tests\Feature\Api\BodyShop\Users;

use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class UserIndexFilterTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_it_only_bs_users(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $this->bsAdminFactory();
        $this->bsAdminFactory();
        $this->bsMechanicFactory();
        $this->dispatcherFactory();

        $bsAdminsCount = User::query()
            ->withoutGlobalScopes()
            ->onlyBodyShopUsers()
            ->withoutBSSuperAdmin()
            ->count();

        $response = $this->getJson(route('body-shop.users.index'))
            ->assertOk();

        $content = json_to_array($response->getContent());
        $this->assertCount($bsAdminsCount, $content['data']);

        $this->loginAsBodyShopAdmin();

        $bsAdminsCount = User::query()
            ->withoutGlobalScopes()
            ->onlyBodyShopUsers()
            ->withoutBSSuperAdmin()
            ->count();

        $response = $this->getJson(route('body-shop.users.index'))
            ->assertOk();

        $content = json_to_array($response->getContent());
        $this->assertCount($bsAdminsCount, $content['data']);
    }

    public function test_it_filter_by_status(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $this->bsAdminFactory(['status' => User::STATUS_ACTIVE]);
        $this->bsAdminFactory(['status' => User::STATUS_ACTIVE]);
        $this->bsAdminFactory(['status' => User::STATUS_PENDING]);

        $response = $this->getJson(route('body-shop.users.index', ['status' => User::STATUS_PENDING]))
            ->assertOk();

        $content = json_to_array($response->getContent());
        $this->assertCount(1, $content['data']);
    }

    public function test_it_search(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $this->bsAdminFactory(['first_name' => 'test123']);
        $this->bsAdminFactory(['last_name' => 'test']);
        $this->bsAdminFactory(['email' => 'test@test.com']);
        $this->bsAdminFactory(['phone' => '12345568']);

        $response = $this->getJson(route('body-shop.users.index', ['name' => 'test']))
            ->assertOk();

        $content = json_to_array($response->getContent());
        $this->assertCount(3, $content['data']);

        $response = $this->getJson(route('body-shop.users.index', ['name' => '123']))
            ->assertOk();

        $content = json_to_array($response->getContent());
        $this->assertCount(2, $content['data']);
    }

    public function test_it_filter_by_role(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $this->bsAdminFactory();
        $this->bsAdminFactory();
        $this->bsMechanicFactory();

        $role = $this->getRoleRepository()->findByName(User::BSMECHANIC_ROLE);

        $response = $this->getJson(route('body-shop.users.index', ['role_id' => $role->id]))
            ->assertOk();

        $content = json_to_array($response->getContent());
        $this->assertCount(1, $content['data']);
    }
}
