<?php

namespace Tests\Feature\Http\Api\V1\Users\UserCrud;

use App\Enums\Users\UserStatus;
use App\Foundations\Modules\Permission\Models\Role;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class ShortListTest extends TestCase
{
    use DatabaseTransactions;

    protected UserBuilder $userBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success_list_by_id()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 User */
        $m_1 = $this->userBuilder->asAdmin()->create();
        $m_2 = $this->userBuilder->asAdmin()->create();
        $m_3 = $this->userBuilder->asMechanic()->create();

        $this->getJson(route('api.v1.users.shortlist', [
            'id' => $m_1->id
        ]))
            ->assertJson([
                'data' => [
                    [
                        'id' => $m_1->id,
                        'first_name' => $m_1->first_name,
                        'last_name' => $m_1->last_name,
                        'phone' => $m_1->phone,
                        'phone_extension' => $m_1->phone_extension,
                        'email' => $m_1->email->getValue(),
                    ],
                ],
            ])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_list_by_role_and_search()
    {
        $this->loginUserAsSuperAdmin();

        $role = Role::query()->admin()->first();

        /** @var $m_1 User */
        $m_1 = $this->userBuilder
            ->firstName('Alex')
            ->lastName('Walles')
            ->asAdmin()->create();
        $m_2 = $this->userBuilder
            ->firstName('Ben')
            ->lastName('Wood')
            ->asAdmin()->create();
        $m_3 = $this->userBuilder
            ->firstName('Smith')
            ->lastName('Walles')
            ->asMechanic()->create();
        $m_4 = $this->userBuilder
            ->firstName('Smith')
            ->lastName('Wood')
            ->asSalesManager()->create();

        $this->getJson(route('api.v1.users.shortlist', [
            'search' => 'Wood',
            'roles' => [$role->id]
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_2->id],
                ],
            ])
            ->assertJsonCount(1, 'data')
        ;
    }

    /** @test */
    public function success_list_by_role_and_statuses()
    {
        $this->loginUserAsSuperAdmin();

        $role = Role::query()->admin()->first();

        /** @var $m_1 User */
        $m_1 = $this->userBuilder
            ->firstName('aaaaa')
            ->lastName('Walles')
            ->status(UserStatus::ACTIVE())
            ->asAdmin()->create();
        $m_2 = $this->userBuilder
            ->firstName('aaaaa')
            ->lastName('Wood')
            ->status(UserStatus::PENDING())
            ->asAdmin()->create();
        $m_3 = $this->userBuilder
            ->firstName('aaaaa')
            ->lastName('Walles')
            ->status(UserStatus::ACTIVE())
            ->asAdmin()->create();
        $m_4 = $this->userBuilder
            ->firstName('aaaaa')
            ->lastName('Wood')
            ->status(UserStatus::ACTIVE())
            ->asSalesManager()->create();

        $this->getJson(route('api.v1.users.shortlist', [
            'search' => 'aaaaa',
            'statuses' => [UserStatus::ACTIVE],
            'roles' => [$role->id]
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_3->id],
                    ['id' => $m_1->id],
                ],
            ])
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_list_by_role_and_search_empty()
    {
        $this->loginUserAsSuperAdmin();

        $role = Role::query()->admin()->first();

        /** @var $m_1 User */
        $m_1 = $this->userBuilder
            ->firstName('Alex')
            ->lastName('Walles')
            ->asAdmin()->create();
        $m_2 = $this->userBuilder
            ->firstName('Ben')
            ->lastName('Wood')
            ->asAdmin()->create();
        $m_3 = $this->userBuilder
            ->firstName('Smith')
            ->lastName('Walles')
            ->asMechanic()->create();

        $this->getJson(route('api.v1.users.shortlist', [
            'search' => 'Root',
            'roles' => [$role->id]
        ]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $role = Role::query()->admin()->first();

        $res = $this->getJson(route('api.v1.users.shortlist', [
            'search' => 'Root',
            'roles' => [$role->id]
        ]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $role = Role::query()->admin()->first();

        $res = $this->getJson(route('api.v1.users.shortlist', [
            'search' => 'Root',
            'roles' => [$role->id]
        ]));

        self::assertUnauthenticatedMessage($res);
    }
}
