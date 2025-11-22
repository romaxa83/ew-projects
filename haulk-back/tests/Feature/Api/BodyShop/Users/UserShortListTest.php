<?php

namespace Api\BodyShop\Users;

use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class UserShortListTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_it_show_all_users_for_super_admin(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $this->bsMechanicFactory(['first_name' => 'testMec1']);
        $this->bsMechanicFactory(['first_name' => 'testMec2']);
        $this->bsAdminFactory(['first_name' => 'testDriver2']);

        $mechanicRole = $this->getRoleRepository()->findByName(User::BSMECHANIC_ROLE);
        $filter = [
            'q' => 'test',
            'roles' => [$mechanicRole->id],
        ];

        $response = $this->getJson(route('body-shop.users.shortlist', $filter))
            ->assertOk();

        $this->assertCount(2, $response['data']);
    }

    public function test_it_search_by_id(): void
    {
        $this->loginAsBodyShopAdmin();

        $mechanic = $this->bsMechanicFactory();
        $this->bsAdminFactory();

        $filter = [
            'searchid' => $mechanic->id,
        ];

        $response = $this->getJson(route('body-shop.users.shortlist', $filter))
            ->assertOk();

        $this->assertCount(1, $response['data']);
    }
}
