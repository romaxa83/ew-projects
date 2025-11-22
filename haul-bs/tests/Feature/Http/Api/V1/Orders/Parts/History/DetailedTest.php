<?php

namespace Tests\Feature\Http\Api\V1\Orders\Parts\History;

use App\Models\Orders\Parts\Order;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\History\HistoryBuilder;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class DetailedTest extends TestCase
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
    public function success_pagination()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $h_1 = $this->historyBuilder->model($model)->create();
        $h_2 = $this->historyBuilder->model($model)->create();
        $this->historyBuilder->create();

        $this->getJson(route('api.v1.orders.parts.detailed-history', ['id' => $model->id]))
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'model_id',
                        'user_id',
                        'message',
                        'meta',
                        'performed_at',
                        'performed_timezone',
                        'histories',
                        'user' => [
                            'full_name',
                            'first_name',
                            'last_name',
                            'email',
                        ]
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    ['id' => $h_2->id,],
                    ['id' => $h_1->id],
                ],
                'meta' => [
                    'total' => 2
                ]
            ])
        ;
    }

    /** @test */
    public function success_by_page()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $this->historyBuilder->model($model)->create();
        $this->historyBuilder->model($model)->create();
        $this->historyBuilder->model($model)->create();
        $this->historyBuilder->create();

        $this->getJson(route('api.v1.orders.parts.detailed-history', [
            'page' => 2,
            'id' => $model->id
        ]))
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

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $this->historyBuilder->model($model)->create();
        $this->historyBuilder->model($model)->create();
        $this->historyBuilder->model($model)->create();
        $this->historyBuilder->create();

        $this->getJson(route('api.v1.orders.parts.detailed-history', [
            'per_page' => 2,
            'id' => $model->id
        ]))
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

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $this->getJson(route('api.v1.orders.parts.detailed-history', ['id' => $model->id]))
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
    public function success_filter_by_user()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $user_1 = $this->userBuilder->create();
        $user_2 = $this->userBuilder->create();

        $details = [
            'vin' => [
                'old' => null,
                'new' => '33ERTFHGXDF567',
                'type' => 'added',
            ],
            'unit_number' => [
                'old' => null,
                'new' => '11WWW222',
                'type' => 'added',
            ],
            'make' => [
                'old' => null,
                'new' => 'FORD',
                'type' => 'added',
            ],
        ];

        $this->historyBuilder->model($model)->user($user_1)->details($details)->create();
        $this->historyBuilder->model($model)->user($user_2)->create();
        $this->historyBuilder->model($model)->user($user_2)->create();
        $this->historyBuilder->create();

        $this->getJson(route('api.v1.orders.parts.detailed-history', [
            'user_id' => $user_1->id,
            'id' => $model->id
        ]))
            ->assertJson([
                'data' => [
                    [
                        'user_id' => $user_1->id,
                        'user' => [
                            'full_name' => $user_1->full_name
                        ],
                        'histories' => $details
                    ]
                ],
                'meta' => [
                    'current_page' => 1,
                    'total' => 1,
                ]
            ])
        ;
    }

    /** @test */
    public function success_filter_by_range_data()
    {
        $this->loginUserAsSuperAdmin();

        $data = CarbonImmutable::now();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $this->historyBuilder->model($model)->performed_at($data->subDays(20))->create();
        $h_1 = $this->historyBuilder->model($model)->performed_at($data->subDays(15))->create();
        $h_2 = $this->historyBuilder->model($model)->performed_at($data->subDays(14))->create();
        $this->historyBuilder->create();

        $this->getJson(route('api.v1.orders.parts.detailed-history', [
            'dates_range' => $data->subDays(18)->format('m/d/Y') . ' - ' . $data->subDays(10)->format('m/d/Y'),
            'id' => $model->id
        ]))
            ->assertJson([
                'data' => [
                    ['id' => $h_2->id,],
                    ['id' => $h_1->id,],
                ],
                'meta' => [
                    'total' => 2,
                ]
            ])
        ;
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->getJson(route('api.v1.orders.parts.detailed-history', ['id' => 0]))
        ;

        self::assertErrorMsg($res, __("exceptions.orders.parts.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->getJson(route('api.v1.orders.parts.detailed-history', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->getJson(route('api.v1.orders.parts.detailed-history', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
