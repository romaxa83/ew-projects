<?php

namespace Tests\Feature\Api\Users\Users;

use App\Models\Tags\Tag;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class UserIndexFilterTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_it_filter_all_except_super_admins_in_user_list()
    {
        $this->loginAsCarrierSuperAdmin();

        $role = $this->getRoleRepository()->findByName(User::SUPERADMIN_ROLE);
        $filter = ['role_id' => $role->id];
        $response = $this->getJson(route('users.index', $filter))
            ->assertOk();

        $users = $response->json('data');

        foreach ($users as $user) {
            $this->assertEquals($role->id, $user['role_id']);
        }
    }

    public function test_it_filter_all_except_drivers_in_user_list()
    {
        $this->loginAsCarrierSuperAdmin();

        $role = $this->getRoleRepository()->findByName(User::DRIVER_ROLE);
        $filter = ['role_id' => $role->id];

        $response = $this->getJson(route('users.index', $filter))
            ->assertOk();

        $content = json_to_array($response->getContent());
        $this->assertNotEmpty($content);

        foreach ($content['data'] as $user) {
            $this->assertEquals($role->id, $user['role_id']);
        }
    }

    public function test_show_only_my_drivers()
    {
        $this->loginAsCarrierSuperAdmin();
        $this->driverFactory(
            [
                'owner_id' => $this->getAuthenticatedUser()->id,
            ]
        );
        $this->dispatcherFactory(
            [
                'owner_id' => $this->getAuthenticatedUser()->id,
            ]
        );

        // no key at all
        $filter = [
            'role_id' => 3,
        ];

        $response = $this->getJson(route('users.index', $filter))
            ->assertOk();

        $content = json_to_array($response->getContent());
        $this->assertNotEmpty($content);

        foreach ($content['data'] as $user) {
            $this->assertEquals(3, $user['role_id']);
        }

        // key set to 0
        $filter = [
            'role_id' => 3,
            'my_drivers' => false,
        ];

        $response = $this->getJson(route('users.index', $filter))
            ->assertOk();

        $content = json_to_array($response->getContent());
        $this->assertNotEmpty($content);

        foreach ($content['data'] as $user) {
            $this->assertEquals(3, $user['role_id']);
        }

        // key set to 1
        $filter = [
            'role_id' => 3,
            'my_drivers' => 1,
        ];

        $response = $this->getJson(route('users.index', $filter))
            ->assertOk()
            ->assertJsonPath('meta.total', 1);

        $content = json_to_array($response->getContent());
        $this->assertNotEmpty($content);

        foreach ($content['data'] as $user) {
            $this->assertEquals($this->getAuthenticatedUser()->id, $user['owner_id']);
        }
    }

    public function test_it_filter_by_status()
    {
        $this->loginAsCarrierSuperAdmin();

        $this->dispatcherFactory(['status' => User::STATUS_ACTIVE, 'owner_id' => $this->getAuthenticatedUser()->id]);
        $this->dispatcherFactory(['status' => User::STATUS_INACTIVE, 'owner_id' => $this->getAuthenticatedUser()->id]);
        $this->dispatcherFactory(['status' => User::STATUS_INACTIVE, 'owner_id' => $this->getAuthenticatedUser()->id]);
        $this->dispatcherFactory(['status' => User::STATUS_INACTIVE, 'owner_id' => $this->getAuthenticatedUser()->id]);
        $this->dispatcherFactory(['status' => User::STATUS_PENDING, 'owner_id' => $this->getAuthenticatedUser()->id]);
        $this->dispatcherFactory(['status' => User::STATUS_PENDING, 'owner_id' => $this->getAuthenticatedUser()->id]);

        $filter = ['status' => User::STATUS_INACTIVE];
        $response = $this->getJson(route('users.index', $filter))
            ->assertOk();

        $content = json_to_array($response->getContent());
        $this->assertNotEmpty($content);
        foreach ($content['data'] as $user) {
            $this->assertEquals(User::STATUS_INACTIVE, $user['status']);
        }
    }

    public function test_it_filter_by_tag(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $user1 = $this->ownerFactory();
        $user2 = $this->ownerFactory();
        $user3 = $this->driverOwnerFactory();

        $tag1 = Tag::factory()->create(['type' => Tag::TYPE_VEHICLE_OWNER]);
        $tag2 = Tag::factory()->create(['type' => Tag::TYPE_VEHICLE_OWNER]);

        $user1->tags()->sync([$tag1->id, $tag2->id]);
        $user3->tags()->sync([$tag1->id]);

        $filter = ['tag_id' => $tag1->id];
        $response = $this->getJson(route('users.index', $filter))
            ->assertOk();

        $content = json_to_array($response->getContent());
        $this->assertCount(2, $content['data']);
    }
}
