<?php

namespace Tests\Feature\Http\Api\V1\Customers\AddressCrud;

use App\Models\Customers\Address;
use App\Models\Customers\Customer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Customers\AddressBuilder;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;

    protected CustomerBuilder $customerBuilder;
    protected AddressBuilder $addressBuilder;

    protected array $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->customerBuilder = resolve(CustomerBuilder::class);
        $this->addressBuilder = resolve(AddressBuilder::class);

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
    public function success_create()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();

        $data = $this->data;

        $this->assertEmpty($customer->addresses);

        $this->postJson(route('api.v1.customers.address.store', [
            'id' => $customer->id
        ]), $data)
            ->assertJson([
                'data' => [
                    'id' => $customer->id,
                    'addresses' => [
                        [
                            'is_default' => $data['is_default'],
                            'from_ecomm' => false,
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

        $customer->refresh();

        $this->assertEquals(
            $customer->addresses[0]->sort,
            $customer->addresses[0]->created_at->timestamp *3
        );
    }

    /** @test */
    public function success_create_as_default()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();
        /** @var $addr Address */
        $addr = $this->addressBuilder->customer($customer)->default()->create();
        $addr->update(['sort' => $addr->created_at->timestamp * 3]);

        $data = $this->data;

        $this->assertTrue($addr->isDefault());

        $this->postJson(route('api.v1.customers.address.store', [
            'id' => $customer->id
        ]), $data)
            ->assertJson([
                'data' => [
                    'id' => $customer->id,
                ],
            ])
            ->assertJsonCount(2, 'data.addresses')
        ;

        $addr->refresh();

        $this->assertFalse($addr->isDefault());
        $this->assertEquals($addr->sort, $addr->created_at->timestamp);

        $customer->refresh();

        $this->assertNotNull($customer->defaultAddress());
    }


    /** @test */
    public function fail_more_limit()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();

        $this->addressBuilder->customer($customer)->create();
        $this->addressBuilder->customer($customer)->create();
        $this->addressBuilder->customer($customer)->create();
        $this->addressBuilder->customer($customer)->create();
        $this->addressBuilder->customer($customer)->create();

        $data = $this->data;

        $this->assertCount(Address::MAX_COUNT, $customer->addresses);

        $res = $this->postJson(route('api.v1.customers.address.store', [
            'id' => $customer->id
        ]), $data)
        ;

        $this->assertErrorMsg($res,
            __('exceptions.customer.address.more_limit'),
            Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function field_wrong_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();

        $data['first_name'] = null;

        $res = $this->postJson(route('api.v1.customers.address.store', [
            'id' => $customer->id
        ]), $data, [
            'Validate-Only' => true
        ])
        ;

        $this->assertValidationMsgWithValidateOnly($res,
            __('validation.required', ['attribute' => __('validation.attributes.first_name')]),
            'first_name');
    }

    /** @test */
    public function field_success_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();

        $data = $this->data;

        $this->assertEmpty($customer->addresses);

        $this->postJson(route('api.v1.customers.address.store', [
            'id' => $customer->id
        ]), $data, [
            'Validate-Only' => true
        ])
            ->assertJsonCount(0, 'data')
        ;

        $this->assertEmpty($customer->addresses);
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

        $this->addressBuilder->phone('11111111111')->customer($customer)->create();

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->postJson(route('api.v1.customers.address.store', [
            'id' => $customer->id
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
    public function fail_not_found_customer()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.customers.address.store', [
            'id' => $customer->id + 1
        ]), $data)
        ;

        self::assertErrorMsg($res, __("exceptions.customer.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.customers.address.store', [
            'id' => $customer->id
        ]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();

        $res = $this->postJson(route('api.v1.customers.address.store', [
            'id' => $customer->id
        ]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
