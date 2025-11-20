<?php

namespace Tests\Feature\Api\User;

use App\Helpers\DateFormat;
use App\Models\JD\Dealer;
use App\Models\User\Role;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class UserOneTest extends TestCase
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
        $dealer = Dealer::query()->first();

        $user = $this->userBuilder
            ->withProfile()
            ->withCountry()
            ->setRole($role)
            ->setDealer($dealer)
            ->create();

        $this->getJson(route('admin.user.show', ['user' => $user]))
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
                    'alias' => $user->country->alias,
                ],
                'dealers' => [
                    [
                        'id' => $dealer->id,
                        'name' => $dealer->name,
                    ]
                ]
            ]))
        ;
    }

    /** @test */
    public function not_admin()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $this->getJson(route('admin.user.show', ['user' => $user]))
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $user = $this->userBuilder->create();

        $this->getJson(route('admin.user.show', ['user' => $user]))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}

