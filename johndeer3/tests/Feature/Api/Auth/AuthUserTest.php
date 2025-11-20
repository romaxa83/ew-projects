<?php

namespace Tests\Feature\Api\Auth;

use App\Helpers\DateFormat;
use App\Models\JD\Dealer;
use App\Models\JD\EquipmentGroup;
use App\Models\User\Role;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Illuminate\Http\Response;
use Tests\Traits\ResponseStructure;

class AuthUserTest extends TestCase
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
    public function success_as_admin()
    {
        $eg_1 = EquipmentGroup::query()->first();
        $eg_2 = EquipmentGroup::query()->where('id', '!=', $eg_1->id)->first();

        /** @var $user User */
        $user = $this->userBuilder
            ->withProfile()
            ->withCountry()
            ->setEgIDs($eg_1->id, $eg_2->id)
            ->create();
        $this->loginAsUser($user);

        $this->getJson(route('api.user'))
            ->assertJson($this->structureSuccessResponse([
                'id' => $user->id,
                'login' => $user->login,
                'email' => $user->email,
                'phone' => $user->phone,
                'status' => $user->status,
                'created' => DateFormat::front($user->created_at),
                'updated' => DateFormat::front($user->updated_at),
                'profile' => [
                    'first_name' => $user->profile->first_name,
                    'last_name' => $user->profile->last_name,
                ],
                'role' => [
                    'role' => $user->getRoleName(),
                    'alias' => $user->getRole(),
                ],
                'lang' => $user->lang,
                'country' => [
                    'id' => $user->country->id,
                    'name' => $user->country->name,
                    'alias' => $user->country->alias
                ],
                'egs' => [
                    [
                        'id' => $eg_1->id,
                        'name' => $eg_1->name,
                    ],
                    [
                        'id' => $eg_2->id,
                        'name' => $eg_2->name,
                    ]
                ]
            ]))
        ;
    }

    /** @test */
    public function success_view_dealer_as_tm()
    {
        $role = Role::query()->where('role', Role::ROLE_TM)->first();

        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();
        /** @var $user User */
        $user = $this->userBuilder
            ->setRole($role)
            ->setDealersIDs($dealer_1->id, $dealer_2->id)
            ->create();
        $this->loginAsUser($user);

        $this->getJson(route('api.user'))
            ->assertJson($this->structureSuccessResponse([
                'id' => $user->id,
                'dealers' => [
                    [
                        'id' => $dealer_1->id,
                        'name' => $dealer_1->name,
                    ],
                    [
                        'id' => $dealer_2->id,
                        'name' => $dealer_2->name,
                    ]
                ]
            ]))
        ;
    }

    /** @test */
    public function success_view_dealer_as_tmd()
    {
        $role = Role::query()->where('role', Role::ROLE_TMD)->first();

        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();
        /** @var $user User */
        $user = $this->userBuilder
            ->setRole($role)
            ->setDealersIDs($dealer_1->id, $dealer_2->id)
            ->create();
        $this->loginAsUser($user);

        $this->getJson(route('api.user'))
            ->assertJson($this->structureSuccessResponse([
                'id' => $user->id,
                'dealers' => [
                    [
                        'id' => $dealer_1->id,
                        'name' => $dealer_1->name,
                    ],
                    [
                        'id' => $dealer_2->id,
                        'name' => $dealer_2->name,
                    ]
                ]
            ]))
        ;
    }

    /** @test */
    public function success_view_dealer_as_pss_not_view()
    {
        $role = Role::query()->where('role', Role::ROLE_PSS)->first();

        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();
        /** @var $user User */
        $user = $this->userBuilder
            ->setRole($role)
            ->setDealersIDs($dealer_1->id, $dealer_2->id)
            ->create();
        $this->loginAsUser($user);

        $this->getJson(route('api.user'))
            ->assertJson($this->structureSuccessResponse([
                'id' => $user->id,
                'dealers' => [
                    0 => null
                ]
            ]))
        ;
    }

    /** @test */
    public function success_view_dealer_as_tm_not_view()
    {
        $role = Role::query()->where('role', Role::ROLE_TM)->first();

        $dealer = Dealer::query()->first();
        /** @var $user User */
        $user = $this->userBuilder
            ->setRole($role)
            ->setDealer($dealer)
            ->create();
        $this->loginAsUser($user);

        $this->getJson(route('api.user'))
            ->assertJson($this->structureSuccessResponse([
                'id' => $user->id,
                'dealers' => []
            ]))
            ->assertJsonCount(0, 'data.dealers')
        ;
    }

    /** @test */
    public function success_admin()
    {
        $password = 'password';
        /** @var $user User */
        $user = $this->userBuilder
            ->setPassword($password)
            ->create();

        $this->assertTrue($user->isAdmin());

        $this->postJson(route('api.login'), [
            'login' => $user->login,
            'password' => $password
        ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure($this->structureTokens())
            ->assertJson(["data" => [
                "isAdmin" => true
            ]])
        ;
    }

    /** @test */
    public function fail_wrong_password()
    {
        $password = 'password';
        /** @var $user User */
        $user = $this->userBuilder
            ->setPassword($password)
            ->create();

        $this->postJson(route('api.login'), [
            'login' => $user->login,
            'password' => $password . '1'
        ])
            ->assertJson($this->structureErrorResponse(__('message.user_wrong_password')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $this->getJson(route('api.user'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}

