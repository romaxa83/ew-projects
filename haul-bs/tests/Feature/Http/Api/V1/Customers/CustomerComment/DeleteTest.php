<?php

namespace Tests\Feature\Http\Api\V1\Customers\CustomerComment;

use App\Models\Customers\Customer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Comments\CommentBuilder;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected CustomerBuilder $customerBuilder;
    protected CommentBuilder $commentBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->customerBuilder = resolve(CustomerBuilder::class);
        $this->commentBuilder = resolve(CommentBuilder::class);
    }

    /** @test */
    public function success_delete()
    {
        $user = $this->loginUserAsSuperAdmin();

        /** @var $model Customer */
        $model = $this->customerBuilder->create();

        $comment = $this->commentBuilder
            ->author($user)
            ->model($model)
            ->create();
        $this->commentBuilder
            ->author($user)
            ->model($model)
            ->create();


        $this->assertCount(2, $model->comments);

        $this->deleteJson(route('api.v1.customers.delete-comment', [
            'id' => $model->id,
            'commentId' => $comment
        ]))
            ->assertNoContent()
        ;

        $model->refresh();

        $this->assertCount(1, $model->comments);
    }

    /** @test */
    public function fail_not_found()
    {
        $user = $this->loginUserAsSuperAdmin();

        /** @var $model Customer */
        $model = $this->customerBuilder->create();

        $comment = $this->commentBuilder
            ->author($user)
            ->model($model)
            ->create();

        $res = $this->deleteJson(route('api.v1.customers.delete-comment', [
            'id' => 0,
            'commentId' => $comment
        ]));

        self::assertErrorMsg($res, __("exceptions.customer.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function fail_not_found_comment()
    {
        $user = $this->loginUserAsSuperAdmin();

        /** @var $model Customer */
        $model = $this->customerBuilder->create();

        $comment = $this->commentBuilder
            ->author($user)
            ->model($model)
            ->create();

        $res = $this->deleteJson(route('api.v1.customers.delete-comment', [
            'id' => $model->id,
            'commentId' => 0
        ]));

        self::assertErrorMsg($res, __("exceptions.comment.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $user = $this->loginUserAsMechanic();

        /** @var $model Customer */
        $model = $this->customerBuilder->create();

        $comment = $this->commentBuilder
            ->author($user)
            ->model($model)
            ->create();

        $res = $this->deleteJson(route('api.v1.customers.delete-comment', [
            'id' => 0,
            'commentId' => $comment
        ]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Customer */
        $model = $this->customerBuilder->create();

        $comment = $this->commentBuilder
            ->model($model)
            ->create();

        $res = $this->deleteJson(route('api.v1.customers.delete-comment', [
            'id' => 0,
            'commentId' => $comment
        ]));

        self::assertUnauthenticatedMessage($res);
    }
}

