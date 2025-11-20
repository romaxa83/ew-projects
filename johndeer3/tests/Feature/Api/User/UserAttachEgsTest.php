<?php

namespace Tests\Feature\Api\User;

use App\Models\JD\EquipmentGroup;
use App\Models\User\Role;
use App\Models\User\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Mockery\MockInterface;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class UserAttachEgsTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    protected $userBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success_attach()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $eg_1 = EquipmentGroup::query()->first();
        $eg_2 = EquipmentGroup::query()->where('id', '!=', $eg_1->id)->first();

        $role = Role::query()->where('role', Role::ROLE_PSS)->first();
        $user = $this->userBuilder->setRole($role)->create();

        $this->assertEmpty($user->egs);

        $this->postJson(route('admin.attach-egs.user', ['user' => $user]), [
            'eg_ids' => [$eg_1->id, $eg_2->id]
        ])
            ->assertJson($this->structureSuccessResponse([
                'id' => $user->id,
                'egs' => [
                    [
                        'id' => $eg_1->id,
                        'name' => $eg_1->name,
                    ],
                    [
                        'id' => $eg_2->id,
                        'name' => $eg_2->name,
                    ]
                ],
            ]))
        ;

        $user->refresh();

        $this->assertNotEmpty($user->egs);
    }

    /** @test */
    public function success_attach_new()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $eg_1 = EquipmentGroup::query()->first();
        $eg_2 = EquipmentGroup::query()->where('id', '!=', $eg_1->id)->first();

        $role = Role::query()->where('role', Role::ROLE_PSS)->first();
        $user = $this->userBuilder->setEgIDs($eg_1->id)->setRole($role)->create();

        $this->assertNotEmpty($user->egs);
        $this->assertEquals($user->egs[0]->id, $eg_1->id);

        $this->postJson(route('admin.attach-egs.user', ['user' => $user]), [
            'eg_ids' => [$eg_2->id]
        ])
            ->assertJson($this->structureSuccessResponse([
                'id' => $user->id,
                'egs' => [
                    [
                        'id' => $eg_2->id,
                        'name' => $eg_2->name,
                    ]
                ],
            ]))
        ;

        $user->refresh();

        $this->assertNotEmpty($user->egs);
        $this->assertEquals($user->egs[0]->id, $eg_2->id);
    }

    /** @test */
    public function success_attach_delete()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $eg_1 = EquipmentGroup::query()->first();

        $role = Role::query()->where('role', Role::ROLE_PSS)->first();
        $user = $this->userBuilder->setEgIDs($eg_1->id)->setRole($role)->create();

        $this->assertNotEmpty($user->egs);

        $this->postJson(route('admin.attach-egs.user', ['user' => $user]), [
            'eg_ids' => []
        ])
            ->assertJson($this->structureSuccessResponse([
                'id' => $user->id,
                'egs' => [],
            ]))
        ;

        $user->refresh();

        $this->assertEmpty($user->egs);
    }

    /** @test */
    public function fail_wrong_eg_id()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $eg_1 = EquipmentGroup::query()->first();

        $role = Role::query()->where('role', Role::ROLE_PSS)->first();
        $user = $this->userBuilder->setEgIDs($eg_1->id)->setRole($role)->create();

        $this->assertNotEmpty($user->egs);

        $this->postJson(route('admin.attach-egs.user', ['user' => $user]), [
            'eg_ids' => [999]
        ])
            ->assertJson($this->structureErrorResponse(["The selected eg_ids.0 is invalid."]))
        ;
    }

    /** @test */
    public function fail_service_return_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->mock(UserService::class, function(MockInterface $mock){
            $mock->shouldReceive("attachEgs")
                ->andThrows(\Exception::class, "some exception message");
        });

        $user = $this->userBuilder->create();

        $this->postJson(route('admin.attach-egs.user', ['user' => $user]), [
            'eg_ids' => []
        ])
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }

    /** @test */
    public function not_admin()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $eg_1 = EquipmentGroup::query()->first();

        $this->postJson(route('admin.attach-egs.user', ['user' => $user]), [
            'eg_ids' => [$eg_1->id]
        ])
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $user = $this->userBuilder->create();

        $eg_1 = EquipmentGroup::query()->first();

        $this->postJson(route('admin.attach-egs.user', ['user' => $user]), [
            'eg_ids' => [$eg_1->id]
        ])
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}


