<?php

namespace Tests\Feature\Http\Api\V1\Users\UserCrud;

use App\Enums\Orders\BS\OrderStatus;
use App\Enums\Users\UserStatus;
use App\Models\Users\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Orders\BS\OrderBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use DatabaseTransactions;

    protected UserBuilder $userBuilder;
    protected OrderBuilder $orderBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->userBuilder = resolve(UserBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
    }

    /** @test */
    public function success_show()
    {
        $this->loginUserAsSuperAdmin();

        $phone = '14444444449';
        $phones = [
            [
                "number" => "15555555555",
                "extension" => "4111"
            ],
            [
                "number" => "14444444444",
                "extension" => "5111"
            ]
        ];

        /** @var $m_1 User */
        $m_1 = $this->userBuilder
            ->status(UserStatus::ACTIVE())
            ->phones($phones)
            ->phone($phone)
            ->asAdmin()
            ->create();

        $this->getJson(route('api.v1.users.show', ['id' => $m_1->id]))
            ->assertJson([
                'data' => [
                    'id' => $m_1->id,
                    'first_name' => $m_1->first_name,
                    'last_name' => $m_1->last_name,
                    'full_name' => $m_1->full_name,
                    'email' => $m_1->email->getValue(),
                    'phone' => $phone,
                    'phone_extension' => $m_1->phone_extension,
                    'phones' => $phones,
                    'status' => $m_1->status,
                    'deleted_at' => null,
                    'role' => [
                        'id' => $m_1->role->id,
                    ],
                    'hasRelatedOpenOrders' => false,
                    'hasRelatedDeletedOrders' => false,
                ],
            ])
        ;
    }

    /** @test */
    public function success_show_has_open_orders_as_mechanic()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 User */
        $m_1 = $this->userBuilder
            ->status(UserStatus::ACTIVE())
            ->asMechanic()
            ->create();

        $this->orderBuilder->status(OrderStatus::In_process->value)->mechanic($m_1)->create();

        $this->getJson(route('api.v1.users.show', ['id' => $m_1->id]))
            ->assertJson([
                'data' => [
                    'id' => $m_1->id,
                    'hasRelatedOpenOrders' => true,
                    'hasRelatedDeletedOrders' => false,
                ],
            ])
        ;
    }

    /** @test */
    public function success_show_has_deleted_orders_as_mechanic()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 User */
        $m_1 = $this->userBuilder
            ->status(UserStatus::ACTIVE())
            ->asMechanic()
            ->create();

        $this->orderBuilder->status(OrderStatus::Deleted->value)->deleted()->mechanic($m_1)->create();

        $this->getJson(route('api.v1.users.show', ['id' => $m_1->id]))
            ->assertJson([
                'data' => [
                    'id' => $m_1->id,
                    'hasRelatedOpenOrders' => false,
                    'hasRelatedDeletedOrders' => true,
                ],
            ])
        ;
    }

    /** @test */
    public function success_show_deleted()
    {
        $this->loginUserAsSuperAdmin();

        $deletedAt = CarbonImmutable::now();

        /** @var $m_1 User */
        $m_1 = $this->userBuilder
            ->status(UserStatus::ACTIVE())
            ->deleted($deletedAt)
            ->asAdmin()
            ->create();

        $this->getJson(route('api.v1.users.show', ['id' => $m_1->id]))
            ->assertJson([
                'data' => [
                    'id' => $m_1->id,
                    'deleted_at' => $deletedAt->timestamp,
                ],
            ])
        ;
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->getJson(route('api.v1.users.show', ['id' => 0]));

        self::assertErrorMsg($res, __("exceptions.user.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $m_1 User */
        $m_1 = $this->userBuilder->asAdmin()->create();

        $res = $this->getJson(route('api.v1.users.show', ['id' => $m_1->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $m_1 User */
        $m_1 = $this->userBuilder->asAdmin()->create();

        $res = $this->getJson(route('api.v1.users.show', ['id' => $m_1->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
