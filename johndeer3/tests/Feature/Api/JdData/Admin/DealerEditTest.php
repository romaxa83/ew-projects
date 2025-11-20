<?php

namespace Tests\Feature\Api\JdData\Admin;

use App\Models\JD\Dealer;
use App\Models\User\Role;
use App\Models\User\User;
use App\Services\JD\DealerService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Illuminate\Http\Response;
use Tests\Traits\ResponseStructure;

class DealerEditTest extends TestCase
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
    public function success()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $user_1 = $this->userBuilder->create();
        $user_2 = $this->userBuilder->create();

        $dealer = Dealer::query()->first();

        $this->assertEmpty($dealer->users);

        $data = [
            'user_ids' => [
                $user_1->id,
                $user_2->id,
            ]
        ];

        $this->postJson(route('admin.dealers.edit', ['dealer' => $dealer]), $data)
            ->assertJson($this->structureSuccessResponse([
                'id' => $dealer->id,
                'name' => $dealer->name,
                'users' => [
                    [
                        'id' => $user_1->id,
                        'login' => $user_1->login,
                    ],
                    [
                        'id' => $user_2->id,
                        'login' => $user_2->login,
                    ]
                ]
            ]))
        ;

        $dealer->refresh();
        $this->assertNotEmpty($dealer->users);
    }

    /** @test */
    public function success_new_users()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $user_1 = $this->userBuilder->create();
        $user_2 = $this->userBuilder->create();

        $dealer = Dealer::query()->first();
        $dealer->users()->attach($user_1);

        $this->assertCount(1, $dealer->users);
        $this->assertEquals($dealer->users[0]->id, $user_1->id);

        $data = [
            'user_ids' => [
                $user_2->id,
            ]
        ];

        $this->postJson(route('admin.dealers.edit', ['dealer' => $dealer]), $data)
            ->assertJson($this->structureSuccessResponse([
                'id' => $dealer->id,
                'name' => $dealer->name,
                'users' => [
                    [
                        'id' => $user_2->id,
                        'login' => $user_2->login,
                    ]
                ]
            ]))
        ;

        $dealer->refresh();
        $this->assertCount(1, $dealer->users);
        $this->assertEquals($dealer->users[0]->id, $user_2->id);
    }

    /** @test */
    public function success_delete_user_as_null()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $user_1 = $this->userBuilder->create();
        $user_2 = $this->userBuilder->create();

        $dealer = Dealer::query()->first();
        $dealer->users()->attach([$user_1->id,$user_2->id]);

        $this->assertCount(2, $dealer->users);

        $data = [
            'user_ids' => null
        ];

        $this->postJson(route('admin.dealers.edit', ['dealer' => $dealer]), $data);

        $dealer->refresh();
        $this->assertCount(0, $dealer->users);
    }

    /** @test */
    public function success_delete_user_as_empty()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $user_1 = $this->userBuilder->create();
        $user_2 = $this->userBuilder->create();

        $dealer = Dealer::query()->first();
        $dealer->users()->attach([$user_1->id,$user_2->id]);

        $this->assertCount(2, $dealer->users);

        $data = [
            'user_ids' => []
        ];

        $this->postJson(route('admin.dealers.edit', ['dealer' => $dealer]), $data);

        $dealer->refresh();
        $this->assertCount(0, $dealer->users);
    }

    /** @test */
    public function fail_not_user_id()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $dealer = Dealer::query()->first();

        $data = [
            'user_ids' => [9999]
        ];

        $this->postJson(route('admin.dealers.edit', ['dealer' => $dealer]), $data)
            ->assertJson($this->structureErrorResponse(['The selected user_ids.0 is invalid.']))
        ;
    }

    /** @test */
    public function fail_country_repo_return_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->mock(DealerService::class, function(MockInterface $mock){
            $mock->shouldReceive("edit")
                ->andThrows(\Exception::class, "some exception message");
        });

        $dealer = Dealer::query()->first();
        $data = ['user_ids' => []];

        $this->postJson(route('admin.dealers.edit', ['dealer' => $dealer]), $data)
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

        $dealer = Dealer::query()->first();

        $this->postJson(route('admin.dealers.edit', ['dealer' => $dealer]), [])
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $dealer = Dealer::query()->first();

        $this->postJson(route('admin.dealers.edit', ['dealer' => $dealer]), [])
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}



