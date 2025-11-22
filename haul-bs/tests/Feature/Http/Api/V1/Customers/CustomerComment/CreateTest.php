<?php

namespace Tests\Feature\Http\Api\V1\Customers\CustomerComment;

use App\Models\Customers\Customer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Comments\CommentBuilder;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;

    protected CustomerBuilder $customerBuilder;
    protected CommentBuilder $commentBuilder;

    protected array $data;

    public function setUp(): void
    {
        parent::setUp();

        $this->customerBuilder = resolve(CustomerBuilder::class);
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

        /** @var $model Customer */
        $model = $this->customerBuilder->create();

        $this->assertCount(0, $model->comments);

        $this->postJson(route('api.v1.customers.add-comment', ['id' => $model->id]), $data)
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

        /** @var $model Customer */
        $model = $this->customerBuilder->create();

        $comment = $this->commentBuilder
            ->author($user)
            ->model($model)
            ->create();


        $this->assertCount(1, $model->comments);

        $this->postJson(route('api.v1.customers.add-comment', ['id' => $model->id]), $data)
        ;

        $model->refresh();

        $this->assertCount(2, $model->comments);
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.customers.add-comment', ['id' => 0]),$data);

        self::assertErrorMsg($res, __("exceptions.customer.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $data = $this->data;

        /** @var $model Customer */
        $model = $this->customerBuilder->create();

        $res = $this->postJson(route('api.v1.customers.add-comment', ['id' => $model->id]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        /** @var $model Customer */
        $model = $this->customerBuilder->create();

        $res = $this->postJson(route('api.v1.customers.add-comment', ['id' => $model->id]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
