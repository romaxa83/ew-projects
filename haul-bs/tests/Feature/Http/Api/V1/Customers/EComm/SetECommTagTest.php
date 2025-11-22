<?php

namespace Feature\Http\Api\V1\Customers\EComm;

use App\Enums\Customers\CustomerType;
use App\Enums\Tags\TagType;
use App\Models\Customers\Customer;
use App\Models\Tags\Tag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\Tags\TagBuilder;
use Tests\TestCase;

class SetECommTagTest extends TestCase
{
    use DatabaseTransactions;

    protected CustomerBuilder $customerBuilder;
    protected TagBuilder $tagBuilder;

    protected $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->tagBuilder = resolve(TagBuilder::class);
        $this->customerBuilder = resolve(CustomerBuilder::class);

        $this->data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@doe.com',
        ];
    }

    /** @test */
    public function success_set()
    {
        $this->loginUserAsSuperAdmin();

        $tag = $this->tagBuilder
            ->name(Tag::ECOM_NAME_TAG)
            ->type(TagType::CUSTOMER())
            ->create();

        $tag_1 = $this->tagBuilder
            ->type(TagType::CUSTOMER())
            ->create();

        /** @var $model Customer */
        $model = $this->customerBuilder
            ->tags($tag_1)
            ->create();

        $this->assertCount(1, $model->tags);

        $this->putJsonEComm(route('api.v1.e_comm.customers.set-ecomm-tag', ['id' => $model->id]), [])
            ->assertJson([
                'data' => [
                ],
            ])
            ->assertJsonCount(2, 'data.tags')
        ;
    }

    /** @test */
    public function success_not_set_if_exists()
    {
        $this->loginUserAsSuperAdmin();

        $tag = $this->tagBuilder
            ->name(Tag::ECOM_NAME_TAG)
            ->type(TagType::CUSTOMER())
            ->create();

        /** @var $model Customer */
        $model = $this->customerBuilder
            ->tags($tag)
            ->create();

        $this->assertCount(1, $model->tags);

        $this->putJsonEComm(route('api.v1.e_comm.customers.set-ecomm-tag', ['id' => $model->id]), [])
            ->assertJson([
                'data' => [
                ],
            ])
            ->assertJsonCount(1, 'data.tags')
        ;
    }

    /** @test */
    public function success_create_and_set()
    {
        $this->loginUserAsSuperAdmin();

        $tag_1 = $this->tagBuilder
            ->type(TagType::CUSTOMER())
            ->create();

        /** @var $model Customer */
        $model = $this->customerBuilder
            ->tags($tag_1)
            ->create();

        $this->assertCount(1, $model->tags);

        $this->putJsonEComm(route('api.v1.e_comm.customers.set-ecomm-tag', ['id' => $model->id]), [])
            ->assertJson([
                'data' => [
                ],
            ])
            ->assertJsonCount(2, 'data.tags')
        ;
    }

    /** @test */
    public function fail_not_ecomm_customer()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Customer */
        $model = $this->customerBuilder
            ->type(CustomerType::BS)
            ->create();

        $res = $this->putJsonEComm(route('api.v1.e_comm.customers.set-ecomm-tag', ['id' => $model->id + 1]))
        ;

        self::assertErrorMsg($res, __("exceptions.customer.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $res = $this->putJsonEComm(route('api.v1.e_comm.customers.set-ecomm-tag', ['id' => 0]), $data)
        ;

        self::assertErrorMsg($res, __("exceptions.customer.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function wrong_token()
    {
        /** @var $model Customer */
        $model = $this->customerBuilder->create();

        $res = $this->putJsonEComm(route('api.v1.e_comm.customers.set-ecomm-tag', ['id' => $model->id]), [], [
            'Authorization' => 'wrong'
        ]);

        self::assertErrorMsg($res, "Wrong e-comm auth-token", Response::HTTP_UNAUTHORIZED);
    }
}
