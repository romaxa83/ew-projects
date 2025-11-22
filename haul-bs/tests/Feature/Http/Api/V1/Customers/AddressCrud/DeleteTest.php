<?php

namespace Tests\Feature\Http\Api\V1\Customers\AddressCrud;

use App\Models\Customers\Address;
use App\Models\Customers\Customer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Customers\AddressBuilder;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected CustomerBuilder $customerBuilder;
    protected AddressBuilder $addressBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->customerBuilder = resolve(CustomerBuilder::class);
        $this->addressBuilder = resolve(AddressBuilder::class);
    }

    /** @test */
    public function success_delete()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();
        /** @var $model Address */
        $model = $this->addressBuilder->customer($customer)->create();

        $id = $model->id;

        $this->deleteJson(route('api.v1.customers.address.delete', [
            'id' => $customer->id,
            'addressId' => $model->id
        ]))
            ->assertNoContent()
        ;

        $customer->refresh();

        $this->assertEmpty($customer->addresses);
        $this->assertFalse(Address::query()->where('id', $id)->exists());
    }

    /** @test */
    public function fail_not_delete_ecomm_address()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();
        /** @var $model Address */
        $model = $this->addressBuilder->customer($customer)->ecomm()->create();

        $this->assertTrue($model->fromEcomm());

        $res = $this->deleteJson(route('api.v1.customers.address.delete', [
            'id' => $customer->id,
            'addressId' => $model->id
        ]))
        ;

        self::assertErrorMsg(
            $res,
            __('exceptions.customer.address.cant_delete_ecomm_address'),
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

        $res = $this->deleteJson(route('api.v1.customers.address.delete', [
            'id' => $customer->id,
            'addressId' => $model->id + 1
        ]));

        self::assertErrorMsg($res, __("exceptions.customer.address.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();
        /** @var $model Address */
        $model = $this->addressBuilder->customer($customer)->create();

        $res = $this->deleteJson(route('api.v1.customers.address.delete', [
            'id' => $customer->id,
            'addressId' => $model->id + 1
        ]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();
        /** @var $model Address */
        $model = $this->addressBuilder->customer($customer)->create();

        $res = $this->deleteJson(route('api.v1.customers.address.delete', [
            'id' => $customer->id,
            'addressId' => $model->id + 1
        ]));

        self::assertUnauthenticatedMessage($res);
    }
}
