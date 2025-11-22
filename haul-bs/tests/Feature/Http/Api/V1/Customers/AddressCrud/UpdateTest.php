<?php

namespace Tests\Feature\Http\Api\V1\Customers\AddressCrud;

use App\Models\Customers\Address;
use App\Models\Customers\Customer;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Customers\AddressBuilder;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;

    protected CustomerBuilder $customerBuilder;
    protected AddressBuilder $addressBuilder;

    protected $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->addressBuilder = resolve(AddressBuilder::class);
        $this->customerBuilder = resolve(CustomerBuilder::class);

        $this->data = [
            'is_default' => true,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'company_name' => 'Sony Inc.',
            'address' => '801 West Dundee Road',
            'city' => 'Arlington Heights',
            'state' => 'CA',
            'zip' => '60004',
            'phone' => '12999999999',
        ];
    }

    /** @test */
    public function success_update()
    {
        $this->loginUserAsSuperAdmin();

        $now = CarbonImmutable::now();

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();
        /** @var $model Address */
        $model = $this->addressBuilder
            ->customer($customer)
            ->setDate('created_at', $now)
            ->create();

        $data = $this->data;

        $this->assertFalse($model->is_default);
        $this->assertNotEquals($model->first_name, data_get($data, 'first_name'));
        $this->assertNotEquals($model->last_name, data_get($data, 'last_name'));
        $this->assertNotEquals($model->company_name, data_get($data, 'company_name'));
        $this->assertNotEquals($model->address, $data['address']);
        $this->assertNotEquals($model->city, $data['city']);
        $this->assertNotEquals($model->state, $data['state']);
        $this->assertNotEquals($model->zip, $data['zip']);
        $this->assertNotEquals($model->phone, data_get($data, 'phone'));

        $this->postJson(route('api.v1.customers.address.update', [
            'id' => $customer->id,
            'addressId' => $model->id
        ]), $data)
            ->assertJson([
                'data' => [
                    'id' => $customer->id,
                    'addresses' => [
                        [
                            'id' => $model->id,
                            'first_name' => $data['first_name'],
                            'last_name' => $data['last_name'],
                            'company_name' => $data['company_name'],
                            'address' => $data['address'],
                            'city' => $data['city'],
                            'state' => $data['state'],
                            'zip' => $data['zip'],
                            'phone' => $data['phone'],
                        ]
                    ]
                ],
            ])
            ->assertJsonCount(1, 'data.addresses')
        ;

        $model->refresh();
        $this->assertTrue($model->is_default);
        $this->assertEquals($model->sort, $model->created_at->timestamp * 3);
    }

    /** @test */
    public function success_update_not_uniq_phone()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();
        /** @var $model Address */
        $model = $this->addressBuilder->customer($customer)->create();

        $data = $this->data;
        $data['phone'] = $model->phone->getValue();

        $this->postJson(route('api.v1.customers.address.update', [
            'id' => $customer->id,
            'addressId' => $model->id
        ]), $data)
            ->assertJson([
                'data' => [
                    'id' => $customer->id,
                ],
            ])
            ->assertJsonCount(1, 'data.addresses')
        ;
    }

    /** @test */
    public function success_update_as_default()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();
        /** @var $model_1 Address */
        $model_1 = $this->addressBuilder->customer($customer)->create();
        $model_2 = $this->addressBuilder->customer($customer)->default()->create();

        $data = $this->data;

        $this->assertFalse($model_1->isDefault());
        $this->assertTrue($model_2->isDefault());

        $this->postJson(route('api.v1.customers.address.update', [
            'id' => $customer->id,
            'addressId' => $model_1->id
        ]), $data)
            ->assertJson([
                'data' => [
                    'id' => $customer->id,
                ],
            ])
            ->assertJsonCount(2, 'data.addresses')
        ;

        $model_1->refresh();
        $model_2->refresh();

        $this->assertTrue($model_1->isDefault());
        $this->assertEquals($model_1->sort, $model_1->created_at->timestamp * 3);

        $this->assertFalse($model_2->isDefault());
        $this->assertEquals($model_2->sort, $model_2->created_at->timestamp);
    }

    /** @test */
    public function fail_not_update_ecomm_address()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();
        /** @var $model_1 Address */
        $model_1 = $this->addressBuilder->ecomm()->customer($customer)->create();

        $data = $this->data;

        $this->assertTrue($model_1->fromEcomm());

        $res = $this->postJson(route('api.v1.customers.address.update', [
            'id' => $customer->id,
            'addressId' => $model_1->id
        ]), $data)
        ;

        self::assertErrorMsg(
            $res,
            __('exceptions.customer.address.cant_edit_ecomm_address'),
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();
        /** @var $model Address */
        $model = $this->addressBuilder->customer($customer)->create();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.customers.address.update', [
            'id' => $customer->id,
            'addressId' => $model->id + 1
        ]), $data)
        ;

        self::assertErrorMsg($res, __("exceptions.customer.address.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function field_wrong_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();
        /** @var $model Address */
        $model = $this->addressBuilder->customer($customer)->create();

        $data['last_name'] = null;

        $res = $this->postJson(route('api.v1.customers.address.update', [
            'id' => $customer->id,
            'addressId' => $model->id
        ]), $data, [
            'Validate-Only' => true
        ])
        ;

        $this->assertValidationMsgWithValidateOnly(
            $res,
            __('validation.required', ['attribute' => __('validation.attributes.last_name')]),
            'last_name'
        );
    }

    /** @test */
    public function field_success_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();
        /** @var $model Address */
        $model = $this->addressBuilder->customer($customer)->create();

        $data = $this->data;

        $this->postJson(route('api.v1.customers.address.update', [
            'id' => $customer->id,
            'addressId' => $model->id
        ]), $data, [
            'Validate-Only' => true
        ])
            ->assertJsonCount(0, 'data')
        ;
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();
        /** @var $model Address */
        $model = $this->addressBuilder->customer($customer)->create();
        $this->addressBuilder->phone('11111111111')->create();

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->postJson(route('api.v1.customers.address.update', [
            'id' => $customer->id,
            'addressId' => $model->id
        ]), $data)
        ;

        self::assertAndTransformValidationMsg($res, $msgKey, $field, $attributes);
    }

    public static function validate(): array
    {
        return [
            ['is_default', null, 'validation.required', ['attribute' => 'validation.attributes.is_default']],
            ['is_default', 'true', 'validation.boolean', ['attribute' => 'validation.attributes.is_default']],
            ['first_name', null, 'validation.required', ['attribute' => 'validation.attributes.first_name']],
            ['first_name', 1111, 'validation.string', ['attribute' => 'validation.attributes.first_name']],
            ['first_name', '1111', 'validation.alpha', ['attribute' => 'validation.attributes.first_name']],
            ['last_name', null, 'validation.required', ['attribute' => 'validation.attributes.last_name']],
            ['last_name', 1111, 'validation.string', ['attribute' => 'validation.attributes.last_name']],
            ['last_name', '1111', 'validation.alpha', ['attribute' => 'validation.attributes.last_name']],
            ['company_name', 1111, 'validation.string', ['attribute' => 'validation.attributes.company_name']],
            ['address', null, 'validation.required', ['attribute' => 'validation.attributes.address']],
            ['city', null, 'validation.required', ['attribute' => 'validation.attributes.city']],
            ['state', null, 'validation.required', ['attribute' => 'validation.attributes.state']],
            ['zip', null, 'validation.required', ['attribute' => 'validation.attributes.zip']],
            ['zip', 1111, 'validation.string', ['attribute' => 'validation.attributes.zip']],
            ['phone', 'wrong', 'validation.custom.phone.phone_rule', ['attribute' => 'validation.attributes.phone']],
            ['phone', null, 'validation.required', ['attribute' => 'validation.attributes.phone']],
        ];
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $data = $this->data;

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();
        /** @var $model Address */
        $model = $this->addressBuilder->customer($customer)->create();

        $res = $this->postJson(route('api.v1.customers.address.update', [
            'id' => $customer->id,
            'addressId' => $model->id
        ]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();
        /** @var $model Address */
        $model = $this->addressBuilder->customer($customer)->create();

        $res = $this->postJson(route('api.v1.customers.address.update', [
            'id' => $customer->id,
            'addressId' => $model->id
        ]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
