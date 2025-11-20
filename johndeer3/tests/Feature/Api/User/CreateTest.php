<?php

namespace Tests\Feature\Api\User;

use App\Jobs\MailSendJob;
use App\Models\JD\Dealer;
use App\Models\JD\EquipmentGroup;
use App\Models\User\IosLink;
use App\Models\User\Nationality;
use App\Models\User\Role;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class CreateTest extends TestCase
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

        $link = IosLink::factory()->create();
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        $country = Nationality::query()->first();
        $dealer = Dealer::query()->first();

        $data = self::data();
        $data['country_id'] = $country->id;
        $data['dealer_id'] = $dealer->id;

        \Queue::fake();

        $this->assertNull($link->user_id);
        $this->assertEquals($link->status, 1);

        $res = $this->postJson(route('admin.create.user'), $data)
            ->assertJson($this->structureSuccessResponse([
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
                'lang' => null,
                'country' => [
                    'id' => $country->id,
                    'name' => $country->name,
                    'alias' => $country->alias
                ],
                'dealers' => [
                    [
                        'id' => $dealer->id,
                        'name' => $dealer->name,
                    ]
                ],
                'egs' => []
            ]))
        ;

        $user = User::query()->where('id', $res->json('data.id'))->first();

        $this->assertNotNull($user);
        $this->assertNotNull($user->password);
        $this->assertEquals($user->ios_link, $link->link);

        $link->refresh();

        $this->assertEquals($link->user_id, $user->id);
        $this->assertEquals($link->status, 0);

        \Queue::assertPushed(MailSendJob::class, function ($job) {
            return $job->data['user'] instanceof User
                && $job->data['type'] == 'password'
                && $job->data['password'] == User::generateRandomPassword()
                ;
        });
    }

    /** @test */
    public function success_pss()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        IosLink::factory()->create();

        $country = Nationality::query()->first();
        $eg_1 = EquipmentGroup::query()->first();
        $eg_2 = EquipmentGroup::query()->where('id', '!=', $eg_1->id)->first();

        $data = self::data();
        $data['role'] = Role::ROLE_PSS;
        $data['country_id'] = $country->id;
        $data['eg_ids'] = [
            $eg_1->id,
            $eg_2->id,
        ];

        \Queue::fake();

        $this->postJson(route('admin.create.user'), $data)
            ->assertJson($this->structureSuccessResponse([
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
    public function success_tmd()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        IosLink::factory()->create();

        $country = Nationality::query()->first();
        $dealer_1 = Dealer::query()->first();
        $dealer_2 = Dealer::query()->where('id', '!=', $dealer_1->id)->first();

        $data = self::data();
        $data['role'] = Role::ROLE_TMD;
        $data['country_id'] = $country->id;
        $data['dealer_ids'] = [
            $dealer_1->id,
            $dealer_2->id,
        ];

        \Queue::fake();

        $this->postJson(route('admin.create.user'), $data)
            ->assertJson($this->structureSuccessResponse([
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
    public function fail_not_ios_link()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $country = Nationality::query()->first();
        $dealer = Dealer::query()->first();

        $data = self::data();
        $data['country_id'] = $country->id;
        $data['dealer_id'] = $dealer->id;

        \Queue::fake();

        $this->postJson(route('admin.create.user'), $data)
            ->assertJson($this->structureErrorResponse("Not empty ios link"))
        ;

        $this->assertNull(User::query()->where('login', $data['login'])->first());
    }

    /** @test */
    public function fail_empty_data()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->postJson(route('admin.create.user'), [])
            ->assertJson($this->structureErrorResponse([
                "The login field is required.",
                "The email field is required.",
                "The phone field is required.",
                "The country id field is required.",
                "The first_name field is required.",
                "The last_name field is required.",
                "The role field is required.",
            ]))
        ;
    }

    /** @test */
    public function fail_without_login()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $dealer = Dealer::query()->first();

        $data = self::data();
        $data['dealer_id'] = $dealer->id;
        unset($data['login']);

        $this->postJson(route('admin.create.user'), $data)
            ->assertJson($this->structureErrorResponse(["The login field is required."]))
        ;
    }

    /** @test */
    public function fail_not_uniq_login()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $dealer = Dealer::query()->first();

        $data = self::data();
        $data['dealer_id'] = $dealer->id;
        $data['login'] = $admin->login;

        $this->postJson(route('admin.create.user'), $data)
            ->assertJson($this->structureErrorResponse(["The login has already been taken."]))
        ;
    }

    /** @test */
    public function fail_wrong_login()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $dealer = Dealer::query()->first();

        $data = self::data();
        $data['dealer_id'] = $dealer->id;
        $data['login'] = 'te.te';

        $this->postJson(route('admin.create.user'), $data)
            ->assertJson($this->structureErrorResponse(["The login format is invalid."]))
        ;
    }

    /** @test */
    public function fail_without_email()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $dealer = Dealer::query()->first();

        $data = self::data();
        $data['dealer_id'] = $dealer->id;
        unset($data['email']);

        $this->postJson(route('admin.create.user'), $data)
            ->assertJson($this->structureErrorResponse(["The email field is required."]))
        ;
    }

    /** @test */
    public function fail_not_uniq_email()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $dealer = Dealer::query()->first();

        $data = self::data();
        $data['dealer_id'] = $dealer->id;
        $data['email'] = $admin->email;

        $this->postJson(route('admin.create.user'), $data)
            ->assertJson($this->structureErrorResponse(["The email has already been taken."]))
        ;
    }

    /** @test */
    public function fail_wrong_email()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $dealer = Dealer::query()->first();

        $data = self::data();
        $data['dealer_id'] = $dealer->id;
        $data['email'] = 'tete';

        $this->postJson(route('admin.create.user'), $data)
            ->assertJson($this->structureErrorResponse(["The email must be a valid email address."]))
        ;
    }

    /** @test */
    public function fail_without_phone()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $dealer = Dealer::query()->first();

        $data = self::data();
        $data['dealer_id'] = $dealer->id;
        unset($data['phone']);

        $this->postJson(route('admin.create.user'), $data)
            ->assertJson($this->structureErrorResponse(["The phone field is required."]))
        ;
    }

    /** @test */
    public function fail_not_uniq_phone()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $dealer = Dealer::query()->first();

        $data = self::data();
        $data['dealer_id'] = $dealer->id;
        $data['phone'] = $admin->phone;

        $this->postJson(route('admin.create.user'), $data)
            ->assertJson($this->structureErrorResponse(["The phone has already been taken."]))
        ;
    }

    /** @test */
    public function fail_wrong_phone()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $dealer = Dealer::query()->first();

        $data = self::data();
        $data['dealer_id'] = $dealer->id;
        $data['phone'] = 'te.te';

        $this->postJson(route('admin.create.user'), $data)
            ->assertJson($this->structureErrorResponse(["The phone format is invalid."]))
        ;
    }

    /** @test */
    public function fail_without_country_id()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $dealer = Dealer::query()->first();

        $data = self::data();
        $data['dealer_id'] = $dealer->id;
        unset($data['country_id']);

        $this->postJson(route('admin.create.user'), $data)
            ->assertJson($this->structureErrorResponse(["The country id field is required."]))
        ;
    }

    /** @test */
    public function fail_wrong_country_id()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $dealer = Dealer::query()->first();

        $data = self::data();
        $data['dealer_id'] = $dealer->id;
        $data['country_id'] = 9999;

        $this->postJson(route('admin.create.user'), $data)
            ->assertJson($this->structureErrorResponse(["The selected country id is invalid."]))
        ;
    }

    /** @test */
    public function fail_without_first_name()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $dealer = Dealer::query()->first();

        $data = self::data();
        $data['dealer_id'] = $dealer->id;
        unset($data['first_name']);

        $this->postJson(route('admin.create.user'), $data)
            ->assertJson($this->structureErrorResponse(["The first_name field is required."]))
        ;
    }

    /** @test */
    public function fail_without_last_name()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $dealer = Dealer::query()->first();

        $data = self::data();
        $data['dealer_id'] = $dealer->id;
        unset($data['last_name']);

        $this->postJson(route('admin.create.user'), $data)
            ->assertJson($this->structureErrorResponse(["The last_name field is required."]))
        ;
    }

    /** @test */
    public function fail_without_role()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $dealer = Dealer::query()->first();

        $data = self::data();
        $data['dealer_id'] = $dealer->id;
        unset($data['role']);

        $this->postJson(route('admin.create.user'), $data)
            ->assertJson($this->structureErrorResponse(["The role field is required."]))
        ;
    }

    /** @test */
    public function fail_wrong_role()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $dealer = Dealer::query()->first();

        $data = self::data();
        $data['dealer_id'] = $dealer->id;
        $data['role'] = 'rolle';

        $this->postJson(route('admin.create.user'), $data)
            ->assertJson($this->structureErrorResponse(["The selected role is invalid."]))
        ;
    }

    /** @test */
    public function fail_role_tm()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $data = self::data();
        $data['role'] = Role::ROLE_TM;

        $this->postJson(route('admin.create.user'), $data)
            ->assertJson($this->structureErrorResponse(["The selected role is invalid."]))
        ;
    }

    /** @test */
    public function fail_role_sm()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $data = self::data();
        $data['role'] = Role::ROLE_SM;

        $this->postJson(route('admin.create.user'), $data)
            ->assertJson($this->structureErrorResponse(["The selected role is invalid."]))
        ;
    }

    /** @test */
    public function fail_role_admin()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $data = self::data();
        $data['role'] = Role::ROLE_ADMIN;

        $this->postJson(route('admin.create.user'), $data)
            ->assertJson($this->structureErrorResponse(["The selected role is invalid."]))
        ;
    }

    /** @test */
    public function fail_without_dealer_id_if_ps()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $data = self::data();
        $data['role'] = Role::ROLE_PS;

        $this->postJson(route('admin.create.user'), $data)
            ->assertJson($this->structureErrorResponse(["The dealer id field is required when role is ps."]))
        ;
    }

    /** @test */
    public function fail_without_eg_ids_if_pss()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $data = self::data();
        $data['role'] = Role::ROLE_PSS;

        $this->postJson(route('admin.create.user'), $data)
            ->assertJson($this->structureErrorResponse(["The eg ids field is required when role is pss."]))
        ;
    }

    /** @test */
    public function fail_without_dealer_ids_if_tmd()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $data = self::data();
        $data['role'] = Role::ROLE_TMD;

        $this->postJson(route('admin.create.user'), $data)
            ->assertJson($this->structureErrorResponse(["The dealer ids field is required when role is tmd."]))
        ;
    }

    /** @test */
    public function not_admin()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $country = Nationality::query()->first();
        $dealer = Dealer::query()->first();

        $data = self::data();
        $data['country_id'] = $country->id;
        $data['dealer_id'] = $dealer->id;

        \Queue::fake();

        $this->postJson(route('admin.create.user'), $data)
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $country = Nationality::query()->first();
        $dealer = Dealer::query()->first();

        $data = self::data();
        $data['country_id'] = $country->id;
        $data['dealer_id'] = $dealer->id;

        $this->postJson(route('admin.create.user'), $data)
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }

    public static function data(): array
    {
        return [
            'login' => 'login',
            'email' => 'test@test.com',
            'phone' => '380954551111',
            'country_id' => 1,
            'first_name' => 'first name',
            'last_name' => 'last name',
            'role' => Role::ROLE_PS
        ];
    }
}



