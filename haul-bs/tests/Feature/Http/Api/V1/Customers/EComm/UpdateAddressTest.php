<?php

namespace Feature\Http\Api\V1\Customers\EComm;

use App\Models\Customers\Address;
use App\Models\Customers\Customer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Customers\AddressBuilder;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\TestCase;

class UpdateAddressTest extends TestCase
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
    public function success_update()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();

        $model = $this->addressBuilder->customer($customer)->create();

        $data = $this->data;

        $this->assertFalse($model->is_default);
        $this->assertNotEquals($model->first_name, data_get($data, 'first_name'));
        $this->assertNotEquals($model->last_name, data_get($data, 'last_name'));
        $this->assertNotEquals($model->address, $data['address']);
        $this->assertNotEquals($model->city, $data['city']);
        $this->assertNotEquals($model->state, $data['state']);
        $this->assertNotEquals($model->zip, $data['zip']);
        $this->assertNotEquals($model->phone, data_get($data, 'phone'));

        $this->putJsonEComm(route('api.v1.e_comm.customers.address.update', [
            'id' => $customer->id,
            'addressId' => $model->id,
        ]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
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

        $model->refresh();
        $this->assertTrue($model->is_default);
        $this->assertEquals($model->sort, $model->created_at->timestamp * 3);
    }

    /** @test */
    public function success_update_as_default()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();
        /** @var $model_1 Address */
        $model_1 = $this->addressBuilder->customer($customer)->create();
        $model_2 = $this->addressBuilder->customer($customer)->ecomm()->default()->create();

        $data = $this->data;

        $this->assertFalse($model_1->isDefault());
        $this->assertTrue($model_2->isDefault());

        $this->putJsonEComm(route('api.v1.e_comm.customers.address.update', [
            'id' => $customer->id,
            'addressId' => $model_1->id,
        ]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model_1->id,
                ],
            ])
        ;

        $model_1->refresh();
        $model_2->refresh();

        $this->assertTrue($model_1->isDefault());
        $this->assertEquals($model_1->sort, $model_1->created_at->timestamp * 3);

        $this->assertFalse($model_2->isDefault());
        $this->assertEquals($model_2->sort, $model_2->created_at->timestamp * 2);
    }

    /**
     * @dataProvider validate
     * @test
     */
    public function validate_data($field, $value, $msgKey, $attributes = [])
    {
        $this->loginUserAsSuperAdmin();

        $customer = $this->customerBuilder->create();

        $model = $this->addressBuilder->customer($customer)->create();
        $this->addressBuilder->phone('11111111111')->create();

        $data = $this->data;
        $data[$field] = $value;

        $res = $this->putJsonEComm(route('api.v1.e_comm.customers.address.update',[
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
    public function wrong_token()
    {
        $data = $this->data;

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();
        $model = $this->addressBuilder->customer($customer)->create();


        $res = $this->putJsonEComm(route('api.v1.e_comm.customers.address.update', [
            'id' => $customer->id,
            'addressId' => $model->id
        ]), $data, [
            'Authorization' => 'wrong'
        ]);

        self::assertErrorMsg($res, "Wrong e-comm auth-token", Response::HTTP_UNAUTHORIZED);
    }
}
