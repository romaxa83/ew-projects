<?php

namespace Tests\Feature\Http\Api\V1\Users\UserCrud;

use App\Enums\Users\UserStatus;
use App\Foundations\Modules\Permission\Repositories\RoleRepository;
use App\Foundations\Modules\Permission\Roles\AdminRole;
use App\Foundations\Modules\Permission\Roles\MechanicRole;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use DatabaseTransactions;

    protected UserBuilder $userBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success_pagination_with_default_sort()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->userBuilder->status(UserStatus::ACTIVE())->asAdmin()->create();
        $m_2 = $this->userBuilder->status(UserStatus::PENDING())->asAdmin()->create();
        $m_3 = $this->userBuilder->status(UserStatus::INACTIVE())->asMechanic()->create();

        $this->getJson(route('api.v1.users'))
            ->assertJson([
                'data' => [
                    ['id' => $m_2->id],
                    ['id' => $m_3->id],
                    ['id' => $m_1->id],
                ],
                'meta' => [
                    'current_page' => 1,
                    'total' => 3,
                    'to' => 3,
                ]
            ])
        ;
    }

    /** @test */
    public function success_by_page()
    {
        $this->loginUserAsSuperAdmin();

        $this->userBuilder->asAdmin()->create();
        $this->userBuilder->asAdmin()->create();
        $this->userBuilder->asMechanic()->create();

        $this->getJson(route('api.v1.users', ['page' => 2]))
            ->assertJson([
                'meta' => [
                    'current_page' => 2,
                    'total' => 3,
                    'to' => null,
                ]
            ])
        ;
    }

    /** @test */
    public function success_by_per_page()
    {
        $this->loginUserAsSuperAdmin();

        $this->userBuilder->asAdmin()->create();
        $this->userBuilder->asAdmin()->create();
        $this->userBuilder->asMechanic()->create();

        $this->getJson(route('api.v1.users', ['per_page' => 2]))
            ->assertJson([
                'meta' => [
                    'current_page' => 1,
                    'total' => 3,
                    'per_page' => 2,
                    'to' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function success_empty()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.users'))
            ->assertJson([
                'meta' => [
                    'current_page' => 1,
                    'total' => 0,
                    'to' => 0,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_id()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 User */
        $m_1 = $this->userBuilder->asAdmin()->create();
        $m_2 = $this->userBuilder->asAdmin()->create();

        $this->getJson(route('api.v1.users', [
            'id' => $m_1->id
        ]))
            ->assertJson([
                'data' => [
                    [
                        'id' => $m_1->id,
                        'full_name' => $m_1->full_name,
                        'first_name' => $m_1->first_name,
                        'last_name' => $m_1->last_name,
                        'email' => $m_1->email->getValue(),
                        'phone' => $m_1->phone,
                        'phone_extension' => $m_1->phone_extension,
                        'status' => $m_1->status,
                        'role' => [
                            'id' => $m_1->role->id,
                        ]
                    ]
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_name()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->userBuilder
            ->firstName('Alex')
            ->lastName('Walls')
            ->asAdmin()->create();
        $m_2 = $this->userBuilder
            ->firstName('Alex')
            ->lastName('Bull')
            ->asAdmin()->create();
        $m_3 = $this->userBuilder
            ->firstName('Green')
            ->lastName('Walls')
            ->asAdmin()->create();

        $this->getJson(route('api.v1.users', [
            'search' => 'Alex'
        ]))
            ->assertJson([
                'meta' => [
                    'total' => 2,
                ]
            ])
        ;

        $this->getJson(route('api.v1.users', [
            'search' => 'Bull'
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_2->id],
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_status()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->userBuilder
            ->status(UserStatus::ACTIVE())
            ->asAdmin()->create();
        $m_2 = $this->userBuilder
            ->status(UserStatus::ACTIVE())
            ->asAdmin()->create();
        $m_3 = $this->userBuilder
            ->status(UserStatus::INACTIVE())
            ->asAdmin()->create();

        $this->getJson(route('api.v1.users', [
            'status' => UserStatus::ACTIVE
        ]))
            ->assertJson([
                'meta' => [
                    'total' => 2,
                ]
            ])
        ;

        $this->getJson(route('api.v1.users', [
            'status' => UserStatus::INACTIVE
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_3->id],
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_role()
    {
        $this->loginUserAsSuperAdmin();

        $roleAdmin = resolve(RoleRepository::class)->getByBaseRole(new AdminRole());
        $roleMechanic = resolve(RoleRepository::class)->getByBaseRole(new MechanicRole());

        $m_1 = $this->userBuilder
            ->asAdmin()->create();
        $m_2 = $this->userBuilder
            ->asAdmin()->create();
        $m_3 = $this->userBuilder
            ->asMechanic()->create();

        $this->getJson(route('api.v1.users', [
            'role_id' => $roleAdmin->id
        ]))
            ->assertJson([
                'meta' => [
                    'total' => 2,
                ]
            ])
        ;

        $this->getJson(route('api.v1.users', [
            'role_id' => $roleMechanic->id
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_3->id],
                ],
                'meta' => [
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_sort_by_email()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->userBuilder->email('ben@gmail.com')->asAdmin()->create();
        $m_2 = $this->userBuilder->email('alex@gmail.com')->asAdmin()->create();
        $m_3 = $this->userBuilder->email('woodo@gmail.com')->asAdmin()->create();

        $this->getJson(route('api.v1.users', [
            'order_by' => 'email',
            'order_type' => 'asc',
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_2->id],
                    ['id' => $m_1->id],
                    ['id' => $m_3->id],
                ],
                'meta' => [
                    'total' => 3,
                ]
            ])
        ;

        $this->getJson(route('api.v1.users', [
            'order_by' => 'email',
            'order_type' => 'desc',
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_3->id],
                    ['id' => $m_1->id],
                    ['id' => $m_2->id],
                ],
                'meta' => [
                    'total' => 3,
                ]
            ])
        ;
    }

    /** @test */
    public function success_sort_by_status()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->userBuilder->status(UserStatus::ACTIVE())->asAdmin()->create();
        $m_2 = $this->userBuilder->status(UserStatus::PENDING())->asAdmin()->create();
        $m_3 = $this->userBuilder->status(UserStatus::INACTIVE())->asAdmin()->create();

        $this->getJson(route('api.v1.users', [
            'order_by' => 'status',
            'order_type' => 'asc'
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_1->id],
                    ['id' => $m_3->id],
                    ['id' => $m_2->id],
                ],
                'meta' => [
                    'total' => 3,
                ]
            ])
        ;

        $this->getJson(route('api.v1.users', [
            'order_by' => 'status',
            'order_type' => 'desc'
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_2->id],
                    ['id' => $m_3->id],
                    ['id' => $m_1->id],
                ],
                'meta' => [
                    'total' => 3,
                ]
            ])
        ;
    }

    /** @test */
    public function success_sort_by_full_name()
    {
        $this->loginUserAsSuperAdmin();

        $m_1 = $this->userBuilder
            ->firstName('Alex')
            ->lastName('Walls')
            ->asAdmin()->create();
        $m_2 = $this->userBuilder
            ->firstName('Alex')
            ->lastName('Bull')
            ->asAdmin()->create();
        $m_3 = $this->userBuilder
            ->firstName('Green')
            ->lastName('Walls')
            ->asAdmin()->create();

        $this->getJson(route('api.v1.users', [
            'order_by' => 'full_name',
            'order_type' => 'asc'
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_2->id],
                    ['id' => $m_1->id],
                    ['id' => $m_3->id],
                ],
                'meta' => [
                    'total' => 3,
                ]
            ])
        ;

        $this->getJson(route('api.v1.users', [
            'order_by' => 'full_name',
            'order_type' => 'desc'
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $m_3->id],
                    ['id' => $m_1->id],
                    ['id' => $m_2->id],
                ],
                'meta' => [
                    'total' => 3,
                ]
            ])
        ;
    }

    /** @test */
    public function fail_not_allowed_sort_field()
    {
        $this->loginUserAsSuperAdmin();

        $this->userBuilder->asAdmin()->create();
        $this->userBuilder->asAdmin()->create();

        $res = $this->getJson(route('api.v1.users', [
            'order_by' => 'created_at',
            'order_type' => 'asc'
        ]))
        ;

        self::assertValidationMsg($res, __('validation.in', ['attribute' => 'order by']), 'order_by');
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $res = $this->getJson(route('api.v1.users'));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.users'));

        self::assertUnauthenticatedMessage($res);
    }
}
