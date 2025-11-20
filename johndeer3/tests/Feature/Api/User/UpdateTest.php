<?php

namespace Tests\Feature\Api\User;

use App\Models\JD\Dealer;
use App\Models\JD\EquipmentGroup;
use App\Models\User\Nationality;
use App\Models\User\Role;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class UpdateTest extends TestCase
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
    public function success_ps()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $country = Nationality::query()->first();
        $countryNew = Nationality::query()->where('id', '!=', $country->id)->first();

        $dealer = Dealer::query()->first();
        $dealerNew = Dealer::query()->where('id', '!=', $dealer->id)->first();

        /** @var $user User */
        $user = $this->userBuilder
            ->setRole($role)
            ->setDealer($dealer)
            ->setCountry($country)
            ->withProfile()
            ->create();

        $data = CreateTest::data();
        $data['country_id'] = $countryNew->id;
        $data['dealer_id'] = $dealerNew->id;

        $this->assertNotEquals($user->login, $data['login']);
        $this->assertNotEquals($user->email, $data['email']);
        $this->assertNotEquals($user->phone, $data['phone']);
        $this->assertNotEquals($user->profile->first_name, $data['first_name']);
        $this->assertNotEquals($user->profile->last_name, $data['last_name']);
        $this->assertNotEquals($user->dealer_id, $data['dealer_id']);
        $this->assertNotEquals($user->country_id, $data['country_id']);

        $this->postJson(route('admin.user.edit', ['user' => $user]), $data)
            ->assertJson($this->structureSuccessResponse([
                'id' => $user->id,
                'login' => data_get($data, 'login'),
                'email' => data_get($data, 'email'),
                'phone' => data_get($data, 'phone'),
                'status' => User::STATUS_ACTIVE,
                'profile' => [
                    'first_name' => data_get($data, 'first_name'),
                    'last_name' => data_get($data, 'last_name')
                ],
                'role' => [
                    'alias' => $role->role
                ],
                'lang' => $user->lang,
                'country' => [
                    'id' => $countryNew->id,
                    'name' => $countryNew->name,
                    'alias' => $countryNew->alias
                ],
                'dealers' => [
                    [
                        'id' => $dealerNew->id,
                        'name' => $dealerNew->name,
                    ]
                ],
                'egs' => []
            ]))
        ;
    }

    /** @test */
    public function success_not_change_dealer()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        $dealer = Dealer::query()->first();

        /** @var $user User */
        $user = $this->userBuilder
            ->setRole($role)
            ->setDealer($dealer)
            ->withProfile()
            ->create();

        $data = CreateTest::data();
        unset($data['dealer_id']);

        $this->assertEquals($user->dealer_id, $dealer->id);

        $this->postJson(route('admin.user.edit', ['user' => $user]), $data)
            ->assertJson($this->structureSuccessResponse(['id' => $user->id,]))
        ;

        $user->refresh();

        $this->assertEquals($user->dealer_id, $dealer->id);
    }

    /** @test */
    public function success_pss()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $eg_1 = EquipmentGroup::query()->first();
        $eg_2 = EquipmentGroup::query()->where('id', '!=', $eg_1->id)->first();

        $role = Role::query()->where('role', Role::ROLE_PSS)->first();

        /** @var $user User */
        $user = $this->userBuilder
            ->setRole($role)
            ->withProfile()
            ->setEgIDs($eg_1->id)
            ->create();

        $data = CreateTest::data();
        $data['eg_ids'] = [
            $eg_2->id,
        ];

        $this->assertCount(1, $user->egs);
        $this->assertEquals($user->egs[0]->id, $eg_1->id);

        $this->postJson(route('admin.user.edit', ['user' => $user]), $data)
            ->assertJson($this->structureSuccessResponse([
                'egs' => [
                    [
                        'id' => $eg_2->id,
                        'name' => $eg_2->name,
                    ],
                ]
            ]))
        ;

        $user->refresh();

        $this->assertCount(1, $user->egs);
        $this->assertEquals($user->egs[0]->id, $eg_2->id);
    }

    /** @test */
    public function fail_add_egs_to_not_pss()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $eg_1 = EquipmentGroup::query()->first();

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        /** @var $user User */
        $user = $this->userBuilder
            ->setRole($role)
            ->withProfile()
            ->create();

        $data = CreateTest::data();
        $data['eg_ids'] = [
            $eg_1->id,
        ];

        $this->postJson(route('admin.user.edit', ['user' => $user]), $data)
            ->assertJson($this->structureSuccessResponse([
                'id' => $user->id,
                'egs' => []
            ]))
        ;
    }

    /** @test */
    public function success_tmd()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();

        $role = Role::query()->where('role', Role::ROLE_TMD)->first();

        /** @var $user User */
        $user = $this->userBuilder
            ->setRole($role)
            ->setDealersIDs($dealer_1->id)
            ->withProfile()
            ->create();

        $data = CreateTest::data();
        $data['dealer_ids'] = [$dealer_2->id];

        $this->assertCount(1, $user->dealers);
        $this->assertEquals($user->dealers[0]->id, $dealer_1->id);


        $this->postJson(route('admin.user.edit', ['user' => $user]), $data)
            ->assertJson($this->structureSuccessResponse([
                'dealers' => [
                    [
                        'id' => $dealer_2->id,
                        'name' => $dealer_2->name,
                    ]
                ]
            ]))
        ;

        $user->refresh();

        $this->assertCount(1, $user->dealers);
        $this->assertEquals($user->dealers[0]->id, $dealer_2->id);
    }

    /** @test */
    public function fail_empty_data_if_ps()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        /** @var $user User */
        $user = $this->userBuilder
            ->setRole($role)
            ->withProfile()
            ->create();

        $data = [];

        $this->postJson(route('admin.user.edit', ['user' => $user]), $data)
            ->assertJson($this->structureErrorResponse([
                "The login field is required.",
                "The email field is required.",
                "The phone field is required.",
                "The country id field is required.",
                "The first_name field is required.",
                "The last_name field is required.",
            ]))
        ;
    }

    /** @test */
    public function fail_empty_data_if_tm()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_TM)->first();

        /** @var $user User */
        $user = $this->userBuilder
            ->setRole($role)
            ->withProfile()
            ->create();

        $data = [];

        $this->postJson(route('admin.user.edit', ['user' => $user]), $data)
            ->assertJson($this->structureErrorResponse([
                "The login field is required.",
                "The email field is required.",
                "The phone field is required.",
                "The country id field is required.",
                "The first_name field is required.",
                "The last_name field is required.",
                "The dealer ids field is required.",
            ]))
        ;
    }

    /** @test */
    public function fail_empty_data_if_pss()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PSS)->first();

        /** @var $user User */
        $user = $this->userBuilder
            ->setRole($role)
            ->withProfile()
            ->create();

        $data = [];

        $this->postJson(route('admin.user.edit', ['user' => $user]), $data)
            ->assertJson($this->structureErrorResponse([
                "The login field is required.",
                "The email field is required.",
                "The phone field is required.",
                "The country id field is required.",
                "The first_name field is required.",
                "The last_name field is required.",
                "The eg ids field is required.",
            ]))
        ;
    }

    /** @test */
    public function fail_without_login()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        /** @var $user User */
        $user = $this->userBuilder
            ->setRole($role)
            ->withProfile()
            ->create();

        $data = CreateTest::data();
        unset($data['login']);

        $this->postJson(route('admin.user.edit', ['user' => $user]), $data)
            ->assertJson($this->structureErrorResponse(["The login field is required."]))
        ;
    }

    /** @test */
    public function fail_not_uniq_login()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        /** @var $user User */
        $user = $this->userBuilder
            ->setRole($role)
            ->withProfile()
            ->create();

        $data = CreateTest::data();
        $data['login'] = $admin->login;

        $this->postJson(route('admin.user.edit', ['user' => $user]), $data)
            ->assertJson($this->structureErrorResponse(["The login has already been taken."]))
        ;
    }

    /** @test */
    public function success_ignore_login()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        /** @var $user User */
        $user = $this->userBuilder
            ->setRole($role)
            ->withProfile()
            ->create();

        $data = CreateTest::data();
        $data['login'] = $user->login;

        $this->postJson(route('admin.user.edit', ['user' => $user]), $data)
            ->assertJson($this->structureSuccessResponse(["id" => $user->id]))
        ;
    }

    /** @test */
    public function fail_wrong_login()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        /** @var $user User */
        $user = $this->userBuilder
            ->setRole($role)
            ->withProfile()
            ->create();

        $data = CreateTest::data();
        $data['login'] = 'te.te';

        $this->postJson(route('admin.user.edit', ['user' => $user]), $data)
            ->assertJson($this->structureErrorResponse(["The login format is invalid."]))
        ;
    }

    /** @test */
    public function fail_without_email()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        /** @var $user User */
        $user = $this->userBuilder
            ->setRole($role)
            ->withProfile()
            ->create();

        $data = CreateTest::data();
        unset($data['email']);

        $this->postJson(route('admin.user.edit', ['user' => $user]), $data)
            ->assertJson($this->structureErrorResponse(["The email field is required."]))
        ;
    }

    /** @test */
    public function fail_not_uniq_email()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        /** @var $user User */
        $user = $this->userBuilder
            ->setRole($role)
            ->withProfile()
            ->create();

        $data = CreateTest::data();
        $data['email'] = $admin->email;

        $this->postJson(route('admin.user.edit', ['user' => $user]), $data)
            ->assertJson($this->structureErrorResponse(["The email has already been taken."]))
        ;
    }

    /** @test */
    public function success_ignore_email()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        /** @var $user User */
        $user = $this->userBuilder
            ->setRole($role)
            ->withProfile()
            ->create();

        $data = CreateTest::data();
        $data['email'] = $user->email;

        $this->postJson(route('admin.user.edit', ['user' => $user]), $data)
            ->assertJson($this->structureSuccessResponse(["id" => $user->id]))
        ;
    }

    /** @test */
    public function fail_wrong_email()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        /** @var $user User */
        $user = $this->userBuilder
            ->setRole($role)
            ->withProfile()
            ->create();

        $data = CreateTest::data();
        $data['email'] = 'tete';

        $this->postJson(route('admin.user.edit', ['user' => $user]), $data)
            ->assertJson($this->structureErrorResponse(["The email must be a valid email address."]))
        ;
    }

    /** @test */
    public function fail_without_phone()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        /** @var $user User */
        $user = $this->userBuilder
            ->setRole($role)
            ->withProfile()
            ->create();

        $data = CreateTest::data();
        unset($data['phone']);

        $this->postJson(route('admin.user.edit', ['user' => $user]), $data)
            ->assertJson($this->structureErrorResponse(["The phone field is required."]))
        ;
    }

    /** @test */
    public function fail_not_uniq_phone()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        /** @var $user User */
        $user = $this->userBuilder
            ->setRole($role)
            ->withProfile()
            ->create();

        $data = CreateTest::data();
        $data['phone'] = $admin->phone;

        $this->postJson(route('admin.user.edit', ['user' => $user]), $data)
            ->assertJson($this->structureErrorResponse(["The phone has already been taken."]))
        ;
    }

    /** @test */
    public function success_ignore_phone()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        /** @var $user User */
        $user = $this->userBuilder
            ->setRole($role)
            ->withProfile()
            ->create();

        $data = CreateTest::data();
        $data['phone'] = $user->phone;

        $this->postJson(route('admin.user.edit', ['user' => $user]), $data)
            ->assertJson($this->structureSuccessResponse(["id" => $user->id]))
        ;
    }

    /** @test */
    public function fail_wrong_phone()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        /** @var $user User */
        $user = $this->userBuilder
            ->setRole($role)
            ->withProfile()
            ->create();

        $data = CreateTest::data();
        $data['phone'] = 'te.te';

        $this->postJson(route('admin.user.edit', ['user' => $user]), $data)
            ->assertJson($this->structureErrorResponse(["The phone format is invalid."]))
        ;
    }

    /** @test */
    public function fail_without_country_id()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        /** @var $user User */
        $user = $this->userBuilder
            ->setRole($role)
            ->withProfile()
            ->create();

        $data = CreateTest::data();
        unset($data['country_id']);

        $this->postJson(route('admin.user.edit', ['user' => $user]), $data)
            ->assertJson($this->structureErrorResponse(["The country id field is required."]))
        ;
    }

    /** @test */
    public function fail_wrong_country_id()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        /** @var $user User */
        $user = $this->userBuilder
            ->setRole($role)
            ->withProfile()
            ->create();

        $data = CreateTest::data();
        $data['country_id'] = 9999;

        $this->postJson(route('admin.user.edit', ['user' => $user]), $data)
            ->assertJson($this->structureErrorResponse(["The selected country id is invalid."]))
        ;
    }

    /** @test */
    public function fail_without_first_name()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        /** @var $user User */
        $user = $this->userBuilder
            ->setRole($role)
            ->withProfile()
            ->create();

        $data = CreateTest::data();
        unset($data['first_name']);

        $this->postJson(route('admin.user.edit', ['user' => $user]), $data)
            ->assertJson($this->structureErrorResponse(["The first_name field is required."]))
        ;
    }

    /** @test */
    public function fail_without_last_name()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        /** @var $user User */
        $user = $this->userBuilder
            ->setRole($role)
            ->withProfile()
            ->create();

        $data = CreateTest::data();
        unset($data['last_name']);

        $this->postJson(route('admin.user.edit', ['user' => $user]), $data)
            ->assertJson($this->structureErrorResponse(["The last_name field is required."]))
        ;
    }

    /** @test */
    public function not_admin()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $userEdit = $this->userBuilder->setRole($role)->create();

        $data = CreateTest::data();

        $this->postJson(route('admin.user.edit', ['user' => $userEdit]), $data)
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();

        $data = CreateTest::data();

        $this->postJson(route('admin.user.edit', ['user' => $user]), $data)
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}



