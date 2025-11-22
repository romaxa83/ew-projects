<?php

namespace Tests\Feature\Http\Api\V1\Orders\Parts\Comment;

use App\Models\Orders\Parts\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Comments\CommentBuilder;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected CommentBuilder $commentBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->commentBuilder = resolve(CommentBuilder::class);
    }

    /** @test */
    public function success_list()
    {
        $user = $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $c_1 = $this->commentBuilder->author($user)->model($model)->create();
        $c_2 = $this->commentBuilder->author($user)->model($model)->create();

        $this->getJson(route('api.v1.orders.parts.list-comment', ['id' => $model->id]))
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'comment',
                        'timestamp',
                        'author' => [
                            'id',
                            'full_name',
                            'role' => [
                                'id',
                                'name'
                            ]
                        ],
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    ['id' => $c_1->id],
                    ['id' => $c_2->id],
                ],
            ])
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_list_empty()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $this->getJson(route('api.v1.orders.parts.list-comment', ['id' => $model->id]))
            ->assertJson([
                'data' => [],
            ])
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->getJson(route('api.v1.orders.parts.list-comment', ['id' => 0]));

        self::assertErrorMsg($res, __("exceptions.orders.parts.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->getJson(route('api.v1.orders.parts.list-comment', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->getJson(route('api.v1.orders.parts.list-comment', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
