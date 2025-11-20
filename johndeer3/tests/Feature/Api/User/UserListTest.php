<?php

namespace Tests\Feature\Api\User;

use App\Models\JD\Dealer;
use App\Models\User\Nationality;
use App\Models\User\Role;
use App\Models\User\User;
use App\Repositories\User\UserRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Mockery\MockInterface;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class UserListTest extends TestCase
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
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $this->userBuilder->setRole($role)->create();
        $this->userBuilder->setRole($role)->create();
        $this->userBuilder->setRole($role)->create();
        $this->userBuilder->setRole($role)->create();
        $this->userBuilder->setRole($role)->create();

        $this->getJson(route('admin.user.index'))
            ->assertJsonStructure([
                "data" => [
                    [
                        "id",
                        "login",
                        "email",
                        "phone",
                        "status",
                        "created",
                        "updated",
                        "profile",
                        "role" => [
                            "role",
                            "alias"
                        ],
                        "lang",
                        "country",
                        "dealers",
                        "egs"
                    ]
                ],
                "links" => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
                "meta" => [
                    "current_page",
                    "from",
                    "last_page",
                    "path",
                    "per_page",
                    "to",
                    "total",
                ]
            ])
            ->assertJson([
                "meta" => [
                    "current_page" => 1,
                    "per_page" => User::DEFAULT_PER_PAGE,
                    "total" => 5
                ]
            ])
        ;
    }

    /** @test */
    public function success_look_as_sm_role()
    {
        $admin = $this->userBuilder->setRole(
            Role::query()->where('role', Role::ROLE_SM)->first()
        )->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $this->userBuilder->setRole($role)->create();
        $this->userBuilder->setRole($role)->create();
        $this->userBuilder->setRole($role)->create();
        $this->userBuilder->setRole($role)->create();
        $this->userBuilder->setRole($role)->create();

        $this->getJson(route('admin.user.index'))
            ->assertJson([
                "meta" => [
                    "total" => 6
                ]
            ])
        ;
    }

    /** @test */
    public function success_query_page()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $this->userBuilder->setRole($role)->create();

        $this->getJson(route('admin.user.index', ['page' => 3]))
            ->assertJson([
                "meta" => [
                    "current_page" => 3
                ]
            ])
        ;
    }

    /** @test */
    public function success_query_per_page()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $this->userBuilder->setRole($role)->create();
        $this->userBuilder->setRole($role)->create();
        $this->userBuilder->setRole($role)->create();
        $this->userBuilder->setRole($role)->create();

        $this->getJson(route('admin.user.index', ['perPage' => 2]))
            ->assertJson([
                "meta" => [
                    "per_page" => 2,
                    "total" => 4
                ]
            ])
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_query_role()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role_ps = Role::query()->where('role', Role::ROLE_PS)->first();
        $role_pss = Role::query()->where('role', Role::ROLE_PSS)->first();

        $this->userBuilder->setRole($role_ps)->create();
        $this->userBuilder->setRole($role_ps)->create();
        $this->userBuilder->setRole($role_ps)->create();
        $this->userBuilder->setRole($role_pss)->create();

        $this->getJson(route('admin.user.index', ['role' => Role::ROLE_PS]))
            ->assertJson([
                "meta" => [
                    "total" => 3
                ]
            ])
        ;

        $this->getJson(route('admin.user.index', ['role' => Role::ROLE_PSS]))
            ->assertJson([
                "meta" => [
                    "total" => 1
                ]
            ])
        ;

        $this->getJson(route('admin.user.index', ['role' => Role::ROLE_ADMIN]))
            ->assertJson([
                "meta" => [
                    "total" => 0
                ]
            ])
        ;

        $this->getJson(route('admin.user.index', ['role' => Role::ROLE_SM]))
            ->assertJson([
                "meta" => [
                    "total" => 0
                ]
            ])
        ;
    }

    /** @test */
    public function success_query_login()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $this->userBuilder->setRole($role)->setLogin('retro')->create();
        $this->userBuilder->setRole($role)->setLogin('retmobile')->create();
        $this->userBuilder->setRole($role)->setLogin('valuu')->create();
        $this->userBuilder->setRole($role)->setLogin('valuu1')->create();

        $this->getJson(route('admin.user.index', ['login' => 'ret']))
            ->assertJson([
                "meta" => [
                    "total" => 2
                ]
            ])
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_query_email()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $email = 'test@tes.com';
        $this->userBuilder->setRole($role)->setEmail($email)->create();
        $this->userBuilder->setRole($role)->create();
        $this->userBuilder->setRole($role)->create();
        $this->userBuilder->setRole($role)->create();

        $this->getJson(route('admin.user.index', ['email' => $email]))
            ->assertJson([
                "meta" => [
                    "total" => 1
                ]
            ])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_query_country()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $country_1 = Nationality::query()->first();
        $country_2 = Nationality::query()->where('id', '!=', $country_1->id)->first();

        $this->userBuilder->setRole($role)->setCountry($country_1->id)->create();
        $this->userBuilder->setRole($role)->setCountry($country_2->id)->create();
        $this->userBuilder->setRole($role)->setCountry($country_2->id)->create();
        $this->userBuilder->setRole($role)->setCountry($country_2->id)->create();

        $this->getJson(route('admin.user.index', ['country_id' => $country_1->id]))
            ->assertJson([
                "meta" => [
                    "total" => 1
                ]
            ])
            ->assertJsonCount(1, 'data')
        ;

        $this->getJson(route('admin.user.index', ['country_id' => $country_2->id]))
            ->assertJson([
                "meta" => [
                    "total" => 3
                ]
            ])
            ->assertJsonCount(3, 'data')
        ;
    }

    /** @test */
    public function success_query_wrong_country_id()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $country_1 = Nationality::query()->first();

        $this->userBuilder->setRole($role)->setCountry($country_1->id)->create();

        $this->getJson(route('admin.user.index', ['country_id' => 9999]))
            ->assertJson([
                "meta" => [
                    "total" => 0
                ]
            ])
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function success_query_dealer_as_ps()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();

        $this->userBuilder->setRole($role)->setDealer($dealer_1)->create();
        $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();
        $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();
        $this->userBuilder->setRole($role)->setDealer($dealer_2)->create();

        $this->getJson(route('admin.user.index', ['dealer' => $dealer_1->name]))
            ->assertJson([
                "meta" => [
                    "total" => 1
                ]
            ])
            ->assertJsonCount(1, 'data')
        ;

        $this->getJson(route('admin.user.index', ['dealer' => $dealer_2->name]))
            ->assertJson([
                "meta" => [
                    "total" => 3
                ]
            ])
            ->assertJsonCount(3, 'data')
        ;

        $this->getJson(route('admin.user.index', ['dealer' => 'empty']))
            ->assertJson([
                "meta" => [
                    "total" => 0
                ]
            ])
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function success_query_dealers_as_tm()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_TM)->first();

        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();
        $dealer_3 = Dealer::query()->where([
            ['id', '!=', $dealer_1->id],
            ['id', '!=', $dealer_2->id]
        ])->first();
        $dealer_4 = Dealer::query()->where([
            ['id', '!=', $dealer_1->id],
            ['id', '!=', $dealer_2->id],
            ['id', '!=', $dealer_3->id]
        ])->first();

        $this->userBuilder->setRole($role)->setDealersIDs($dealer_1->id)->create();
        $this->userBuilder->setRole($role)->setDealersIDs($dealer_2->id, $dealer_3->id)->create();
        $this->userBuilder->setRole($role)->setDealersIDs($dealer_2->id, $dealer_3->id)->create();
        $this->userBuilder->setRole($role)->setDealersIDs($dealer_2->id)->create();

        $this->getJson(route('admin.user.index', ['dealer' => $dealer_1->name]))
            ->assertJson([
                "meta" => [
                    "total" => 1
                ]
            ])
            ->assertJsonCount(1, 'data')
        ;

        $this->getJson(route('admin.user.index', ['dealer' => $dealer_2->name]))
            ->assertJson([
                "meta" => [
                    "total" => 3
                ]
            ])
            ->assertJsonCount(3, 'data')
        ;

        $this->getJson(route('admin.user.index', ['dealer' => $dealer_3->name]))
            ->assertJson([
                "meta" => [
                    "total" => 2
                ]
            ])
            ->assertJsonCount(2, 'data')
        ;

        $this->getJson(route('admin.user.index', ['dealer' => $dealer_4->name]))
            ->assertJson([
                "meta" => [
                    "total" => 0
                ]
            ])
            ->assertJsonCount(0, 'data')
        ;

        $this->getJson(route('admin.user.index', ['dealer' => 'empty']))
            ->assertJson([
                "meta" => [
                    "total" => 0
                ]
            ])
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function success_query_name()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $this->userBuilder->setRole($role)->profileData([
            'first_name' => 'Taras',
            'last_name' => 'Shevchenko'
        ])->create();
        $this->userBuilder->setRole($role)->profileData([
            'first_name' => 'Taras',
            'last_name' => 'Hvuliovui'
        ])->create();
        $this->userBuilder->setRole($role)->withProfile()->create();
        $this->userBuilder->setRole($role)->withProfile()->create();

        $this->getJson(route('admin.user.index', ['name' => 'Tara']))
            ->assertJson([
                "meta" => [
                    "total" => 2
                ]
            ])
            ->assertJsonCount(2, 'data')
        ;

        $this->getJson(route('admin.user.index', ['name' => 'Tara_Shevc']))
            ->assertJson([
                "meta" => [
                    "total" => 1
                ]
            ])
            ->assertJsonCount(1, 'data')
        ;

        $this->getJson(route('admin.user.index', ['name' => 'Shevchenko']))
            ->assertJson([
                "meta" => [
                    "total" => 1
                ]
            ])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function fail_look_as_ps_role()
    {
        $admin = $this->userBuilder->setRole(
            Role::query()->where('role', Role::ROLE_PS)->first()
        )->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $this->userBuilder->setRole($role)->create();

        $this->getJson(route('admin.user.index'))
            ->assertJson($this->structureErrorResponse("This action is unauthorized."))
        ;
    }

    /** @test */
    public function fail_look_as_tm_role()
    {
        $admin = $this->userBuilder->setRole(
            Role::query()->where('role', Role::ROLE_TM)->first()
        )->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $this->userBuilder->setRole($role)->create();

        $this->getJson(route('admin.user.index'))
            ->assertJson($this->structureErrorResponse("This action is unauthorized."))
        ;
    }

    /** @test */
    public function fail_look_as_tmd_role()
    {
        $admin = $this->userBuilder->setRole(
            Role::query()->where('role', Role::ROLE_TMD)->first()
        )->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $this->userBuilder->setRole($role)->create();

        $this->getJson(route('admin.user.index'))
            ->assertJson($this->structureErrorResponse("This action is unauthorized."))
        ;
    }

    /** @test */
    public function fail_look_as_pss_role()
    {
        $admin = $this->userBuilder->setRole(
            Role::query()->where('role', Role::ROLE_PSS)->first()
        )->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $this->userBuilder->setRole($role)->create();

        $this->getJson(route('admin.user.index'))
            ->assertJson($this->structureErrorResponse("This action is unauthorized."))
        ;
    }

    /** @test */
    public function fail_repo_return_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->mock(UserRepository::class, function(MockInterface $mock){
            $mock->shouldReceive("getAllForAdmin")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->getJson(route('admin.user.index'))
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $this->getJson(route('admin.user.index'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}

