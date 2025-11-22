<?php

namespace Tests\Feature\Http\Api\V1\Orders\BS\History;

use App\Models\Orders\BS\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\History\HistoryBuilder;
use Tests\Builders\Orders\BS\OrderBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class UserListTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected UserBuilder $userBuilder;
    protected HistoryBuilder $historyBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->historyBuilder = resolve(HistoryBuilder::class);
    }

    /** @test */
    public function success_list()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $user_1 = $this->userBuilder->firstName('Root')->create();
        $user_2 = $this->userBuilder->firstName('Alen')->create();

        $h_1 = $this->historyBuilder->model($model)->user($user_1)->create();
        $h_2 = $this->historyBuilder->model($model)->user($user_2)->create();
        $this->historyBuilder->create();

        $this->getJson(route('api.v1.orders.bs.history-users-list', ['id' => $model->id]))
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'first_name',
                        'last_name',
                        'phone',
                        'phone_extension',
                        'email',
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    ['id' => $user_2->id],
                    ['id' => $user_1->id],
                ]
            ])
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_empty()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $this->historyBuilder->create();

        $this->getJson(route('api.v1.orders.bs.history-users-list', ['id' => $model->id]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->getJson(route('api.v1.orders.bs.history-users-list', ['id' => 0]))
        ;

        self::assertErrorMsg($res, __("exceptions.orders.bs.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->getJson(route('api.v1.orders.bs.history-users-list', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->getJson(route('api.v1.orders.bs.history-users-list', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
