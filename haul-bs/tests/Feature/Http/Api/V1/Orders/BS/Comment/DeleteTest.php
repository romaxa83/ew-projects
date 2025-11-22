<?php

namespace Tests\Feature\Http\Api\V1\Orders\BS\Comment;

use App\Foundations\Modules\History\Enums\HistoryType;
use App\Models\Orders\BS\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Comments\CommentBuilder;
use Tests\Builders\Orders\BS\OrderBuilder;
use Tests\TestCase;

class DeleteTest extends TestCase
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
    public function success_delete()
    {
        $user = $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $comment = $this->commentBuilder
            ->author($user)
            ->model($model)
            ->create();
        $this->commentBuilder
            ->author($user)
            ->model($model)
            ->create();

        $this->assertCount(2, $model->comments);

        $this->deleteJson(route('api.v1.orders.bs.delete-comment', [
            'id' => $model->id,
            'commentId' => $comment
        ]))
            ->assertNoContent()
        ;

        $model->refresh();

        $this->assertCount(1, $model->comments);
    }

    /** @test */
    public function success_check_history()
    {
        $user = $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $comment = $this->commentBuilder
            ->author($user)
            ->model($model)
            ->create();
        $this->commentBuilder
            ->author($user)
            ->model($model)
            ->create();

        $old = clone $comment;

        $this->assertCount(2, $model->comments);

        $this->deleteJson(route('api.v1.orders.bs.delete-comment', [
            'id' => $model->id,
            'commentId' => $comment
        ]))
            ->assertNoContent()
        ;

        $model->refresh();

        $history = $model->histories[0];
        $comment = $old;

        $this->assertEquals($history->type, HistoryType::CHANGES);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.comment.deleted');
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
                'new' => null,
                'old' => $comment->text,
                'type' => 'removed',
            ],
        ]);
    }

    /** @test */
    public function fail_not_found()
    {
        $user = $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $comment = $this->commentBuilder
            ->author($user)
            ->model($model)
            ->create();

        $res = $this->deleteJson(route('api.v1.orders.bs.delete-comment', [
            'id' => 0,
            'commentId' => $comment
        ]));

        self::assertErrorMsg($res, __("exceptions.orders.bs.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function fail_not_found_comment()
    {
        $user = $this->loginUserAsSuperAdmin();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $comment = $this->commentBuilder
            ->author($user)
            ->model($model)
            ->create();

        $res = $this->deleteJson(route('api.v1.orders.bs.delete-comment', [
            'id' => $model->id,
            'commentId' => 0
        ]));

        self::assertErrorMsg($res, __("exceptions.comment.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $user = $this->loginUserAsMechanic();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $comment = $this->commentBuilder
            ->author($user)
            ->model($model)
            ->create();

        $res = $this->deleteJson(route('api.v1.orders.bs.delete-comment', [
            'id' => 0,
            'commentId' => $comment
        ]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $comment = $this->commentBuilder
            ->model($model)
            ->create();

        $res = $this->deleteJson(route('api.v1.orders.bs.delete-comment', [
            'id' => 0,
            'commentId' => $comment
        ]));

        self::assertUnauthenticatedMessage($res);
    }
}
