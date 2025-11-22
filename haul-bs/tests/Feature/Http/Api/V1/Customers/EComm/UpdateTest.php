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

class UpdateTest extends TestCase
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
    public function success_update()
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
            ->type(CustomerType::EComm)
            ->hasEcommAccount()
            ->tags($tag, $tag_1)
            ->create();

        $data = $this->data;

        $this->assertNotEquals($model->first_name, data_get($data, 'first_name'));
        $this->assertNotEquals($model->last_name, data_get($data, 'first_name'));
        $this->assertNotEquals($model->email, data_get($data, 'email'));


        $this->putJsonEComm(route('api.v1.e_comm.customers.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'first_name' => data_get($data, 'first_name'),
                    'last_name' => data_get($data, 'last_name'),
                    'email' => data_get($data, 'email'),
                    'phone' => $model->phone,
                    'phone_extension' => $model->phone_extension,
                    'phones' => $model->phones,
                    'notes' => $model->notes,
                ],
            ])
            ->assertJsonCount(2, 'data.tags')
        ;
    }

    /** @test */
    public function success_update_not_uniq_email()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Customer */
        $model = $this->customerBuilder->hasEcommAccount()->create();

        $data = $this->data;
        $data['email'] = $model->email->getValue();

        $this->putJsonEComm(route('api.v1.e_comm.customers.update', ['id' => $model->id]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                ],
            ])
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

        $data = $this->data;

        $res = $this->putJsonEComm(route('api.v1.e_comm.customers.update', ['id' => $model->id]), $data)
        ;

        self::assertErrorMsg($res, __("exceptions.customer.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $res = $this->putJsonEComm(route('api.v1.e_comm.customers.update', ['id' => 0]), $data)
        ;

        self::assertErrorMsg($res, __("exceptions.customer.not_found"), Response::HTTP_NOT_FOUND);
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        $this->customerBuilder
            ->email('test@test.com')
            ->phone('11111111111')
            ->create();

        /** @var $model Customer */
        $model = $this->customerBuilder->create();

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->putJsonEComm(route('api.v1.e_comm.customers.update', ['id' => $model->id]), $data)
        ;

        self::assertAndTransformValidationMsg($res, $msgKey, $field, $attributes);
    }

    public static function validate(): array
    {
        return [
            ['first_name', null, 'validation.required', ['attribute' => 'validation.attributes.first_name']],
            ['first_name', 1111, 'validation.string', ['attribute' => 'validation.attributes.first_name']],
            ['first_name', '1111', 'validation.alpha', ['attribute' => 'validation.attributes.first_name']],
            ['last_name', null, 'validation.required', ['attribute' => 'validation.attributes.last_name']],
            ['last_name', 1111, 'validation.string', ['attribute' => 'validation.attributes.last_name']],
            ['last_name', '1111', 'validation.alpha', ['attribute' => 'validation.attributes.last_name']],
            ['email', null, 'validation.required', ['attribute' => 'validation.attributes.email']],
            ['email', 'wrong', 'validation.email', ['attribute' => 'validation.attributes.email']],
        ];
    }

    /** @test */
    public function wrong_token()
    {
        /** @var $model Customer */
        $model = $this->customerBuilder->create();

        $data = $this->data;

        $res = $this->putJsonEComm(route('api.v1.e_comm.customers.update', ['id' => $model->id]), $data, [
            'Authorization' => 'wrong'
        ]);

        self::assertErrorMsg($res, "Wrong e-comm auth-token", Response::HTTP_UNAUTHORIZED);
    }
}
