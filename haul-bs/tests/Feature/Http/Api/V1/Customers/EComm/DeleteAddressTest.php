<?php

namespace Feature\Http\Api\V1\Customers\EComm;

use App\Models\Customers\Customer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Customers\AddressBuilder;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\TestCase;

class DeleteAddressTest extends TestCase
{
    use DatabaseTransactions;

    protected CustomerBuilder $customerBuilder;
    protected AddressBuilder $addressBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->addressBuilder = resolve(AddressBuilder::class);
        $this->customerBuilder = resolve(CustomerBuilder::class);
    }

    /** @test */
    public function success_delete()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();

        $model = $this->addressBuilder->customer($customer)->create();

        $this->assertCount(1, $customer->addresses);

        $this->deleteJsonEComm(route('api.v1.e_comm.customers.address.delete', [
            'id' => $customer->id,
            'addressId' => $model->id,
        ]))
            ->assertNoContent()
        ;

        $customer->refresh();

        $this->assertCount(0, $customer->addresses);
    }
    /** @test */
    public function wrong_token()
    {
        /** @var $customer Customer */
        $customer = $this->customerBuilder->create();
        $model = $this->addressBuilder->customer($customer)->create();

        $res = $this->deleteJsonEComm(route('api.v1.e_comm.customers.address.delete', [
            'id' => $customer->id,
            'addressId' => $model->id
        ]), [], [
            'Authorization' => 'wrong'
        ]);

        self::assertErrorMsg($res, "Wrong e-comm auth-token", Response::HTTP_UNAUTHORIZED);
    }
}
