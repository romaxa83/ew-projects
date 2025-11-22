<?php

namespace Tests\Feature\Http\Api\V1\Orders\BS\Comment;

use App\Foundations\Modules\History\Enums\HistoryType;
use App\Models\Orders\BS\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Comments\CommentBuilder;
use Tests\Builders\Orders\BS\OrderBuilder;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected CommentBuilder $commentBuilder;

    protected array $data;

    public function setUp(): void
    {
        parent::setUp();

        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->commentBuilder = resolve(CommentBuilder::class);

        $this->data = [
            'comment' => 'some comment'
        ];
    }

    /** @test */
    public function success_create()
    {
        $user = $this->loginUserAsSuperAdmin();

        $data = $this->data;

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $this->assertCount(0, $model->comments);

        $this->postJson(route('api.v1.orders.bs.add-comment', ['id' => $model->id]), $data)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'comment',
                    'author' => [
                        'id',
                        'full_name',
                        'role' => [
                            'id',
                            'name'
                        ]
                    ],
                ]
            ])
            ->assertJson([
                'data' => [
                    'comment' => $data['comment'],
                    'author' => [
                        'id' => $user->id
                    ]
                ],
            ])
        ;

        $model->refresh();

        $this->assertCount(1, $model->comments);
    }

    /** @test */
    public function success_add_more()
    {
        $user = $this->loginUserAsSuperAdmin();

        $data = $this->data;

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $this->commentBuilder
            ->author($user)
            ->model($model)
            ->create();

        $this->assertCount(1, $model->comments);

        $this->postJson(route('api.v1.orders.bs.add-comment', ['id' => $model->id]), $data)
        ;

        $model->refresh();

        $this->assertCount(2, $model->comments);
    }

    /** @test */
    public function success_check_history()
    {
        $user = $this->loginUserAsSuperAdmin();

        $data = $this->data;

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $this->assertCount(0, $model->comments);

        $this->postJson(route('api.v1.orders.bs.add-comment', ['id' => $model->id]), $data)
        ;

        $model->refresh();

        $history = $model->histories[0];
        $comment = $model->comments[0];

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.comment.created');
        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'order_number' => $model->order_number,
            'order_id' => $model->id,
            'user_id' => $user->id,
        ]);

        $this->assertEquals($history->details, [
            "comments.{$comment->id}.comment" => [
                'old' => null,
                'new' => $comment->text,
                'type' => 'added',
            ],
        ]);
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.orders.bs.add-comment', ['id' => 0]),$data);

        self::assertErrorMsg($res, __("exceptions.orders.bs.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $data = $this->data;

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->postJson(route('api.v1.orders.bs.add-comment', ['id' => $model->id]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $res = $this->postJson(route('api.v1.orders.bs.add-comment', ['id' => $model->id]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
