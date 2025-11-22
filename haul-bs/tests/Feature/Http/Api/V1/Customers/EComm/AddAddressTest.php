<?php

namespace Feature\Http\Api\V1\Customers\EComm;

use App\Models\Customers\Address;
use App\Models\Customers\Customer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Customers\AddressBuilder;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\TestCase;

class AddAddressTest extends TestCase
{
    use DatabaseTransactions;

    protected CustomerBuilder $customerBuilder;
    protected AddressBuilder $addressBuilder;

    protected array $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->addressBuilder = resolve(AddressBuilder::class);
        $this->customerBuilder = resolve(CustomerBuilder::class);

        $this->data = [
            'is_default' => true,
            'first_name' => 'John',
            'last_name' => 'Doe',
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

        $this->postJsonEComm(route('api.v1.e_comm.customers.address.store', [
            'id' => $customer->id
        ]), $data)
            ->assertJson([
                'data' => [
                    'is_default' => $data['is_default'],
                    'from_ecomm' => true,
                    'first_name' => data_get($data, 'first_name'),
                    'last_name' => data_get($data, 'last_name'),
                    'address' => $data['address'],
                    'city' => $data['city'],
                    'state' => $data['state'],
                    'zip' => $data['zip'],
                    'phone' => $data['phone'],
                ],
            ])
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
        $addr = $this->addressBuilder->customer($customer)->ecomm()->default()->create();

        $data = $this->data;

        $this->assertTrue($addr->isDefault());

        $this->postJsonEComm(route('api.v1.e_comm.customers.address.store', [
            'id' => $customer->id
        ]), $data)
            ->assertJson([
                'data' => [
                    'is_default' => $data['is_default'],
                    'first_name' => data_get($data, 'first_name'),
                    'last_name' => data_get($data, 'last_name'),
                    'address' => $data['address'],
                    'city' => $data['city'],
                    'state' => $data['state'],
                    'zip' => $data['zip'],
                    'phone' => $data['phone'],
                ],
            ])
        ;

        $addr->refresh();
        $this->assertFalse($addr->isDefault());
        $this->assertEquals($addr->sort, $addr->created_at->timestamp * 2);

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

        $res = $this->postJsonEComm(route('api.v1.e_comm.customers.address.store', [
            'id' => $customer->id
        ]), $data)
        ;

        $this->assertErrorMsg($res,
            __('exceptions.tag.more_limit'),
            Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        $customer = $this->customerBuilder->create();

        $this->addressBuilder->phone('11111111111')->customer($customer)->create();

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->postJsonEComm(route('api.v1.e_comm.customers.address.store',[
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
            ['address', null, 'validation.required', ['attribute' => 'validation.attributes.address']],
            ['city', null, 'validation.required', ['attribute' => 'validation.attributes.city']],
            ['state', null, 'validation.required', ['attribute' => 'validation.attributes.state']],
            ['zip', null, 'validation.required', ['attribute' => 'validation.attributes.zip']],
            ['zip', 1111, 'validation.string', ['attribute' => 'validation.attributes.zip']],
            ['phone', 'wrong', 'validation.custom.phone.phone_rule', ['attribute' => 'validation.attributes.phone']],
            ['phone', null, 'validation.required', ['attribute' => 'validation.attributes.phone']],
//            ['phone', '11111111111', 'validation.unique', ['attribute' => 'validation.attributes.phone']],
        ];
    }

    /** @test */
    public function wrong_token()
    {
        $data = $this->data;

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();

        $res = $this->postJsonEComm(route('api.v1.e_comm.customers.address.store', [
            'id' => $customer->id
        ]), $data, [
            'Authorization' => 'wrong'
        ]);

        self::assertErrorMsg($res, "Wrong e-comm auth-token", Response::HTTP_UNAUTHORIZED);
    }
}
