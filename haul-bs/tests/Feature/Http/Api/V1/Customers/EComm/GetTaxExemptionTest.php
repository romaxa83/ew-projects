<?php

namespace Feature\Http\Api\V1\Customers\EComm;

use App\Enums\Customers\CustomerType;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerTaxExemption;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\Tags\TagBuilder;
use Tests\TestCase;

class GetTaxExemptionTest extends TestCase
{
    use DatabaseTransactions;

    protected CustomerBuilder $customerBuilder;
    protected TagBuilder $tagBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->customerBuilder = resolve(CustomerBuilder::class);
    }

    /** @test */
    public function success_get()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Customer */
        $model = $this->customerBuilder
            ->type(CustomerType::EComm)
            ->create();

        $taxExemption = CustomerTaxExemption::factory()->for($model)->create();

        $this->getJsonEComm(route('api.v1.e_comm.customers.get-tax-exemption', ['email' => $model->email]))
            ->assertJson([
                'data' => [
                    'id' => $taxExemption->id,
                    'date_active_to' => $taxExemption->date_active_to->timestamp,
                    'status' => $taxExemption->status->value,
                    'type' => $taxExemption->type->value,
                    'link' => $taxExemption->link,
                    'file_name' => $taxExemption->file_name,
                    'file' => $taxExemption->file,
                ],
            ]);

        $this->assertDatabaseCount(CustomerTaxExemption::TABLE, 1);
    }

    /** @test */
    public function success_get_empty()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Customer */
        $model = $this->customerBuilder
            ->type(CustomerType::EComm)
            ->create();

        $this->getJsonEComm(route('api.v1.e_comm.customers.get-tax-exemption', ['email' => $model->email]))
            ->assertJson([
                'data' => null
            ]);

        $this->assertDatabaseCount(CustomerTaxExemption::TABLE, 0);
    }

    /** @test */
    public function success_not_ecomm_customer()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Customer */
        $model = $this->customerBuilder
            ->type(CustomerType::BS)
            ->create();
        $taxExemption = CustomerTaxExemption::factory()->for($model)->create();

        $this->getJsonEComm(route('api.v1.e_comm.customers.get-tax-exemption', ['email' => $model->email]))
            ->assertJson([
                'data' => [
                    'id' => $taxExemption->id,
                    'date_active_to' => $taxExemption->date_active_to->timestamp,
                    'status' => $taxExemption->status->value,
                    'type' => $taxExemption->type->value,
                    'link' => $taxExemption->link,
                    'file_name' => $taxExemption->file_name,
                    'file' => $taxExemption->file,
                ],
            ]);

        $this->assertDatabaseCount(CustomerTaxExemption::TABLE, 1);
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->getJsonEComm(route('api.v1.e_comm.customers.get-tax-exemption', ['email' => 'test@gmail.com']));

        self::assertErrorMsg($res, __("exceptions.customer.not_found"), Response::HTTP_NOT_FOUND);
    }


    /** @test */
    public function wrong_token()
    {
        /** @var $model Customer */
        $model = $this->customerBuilder->create();

        $res = $this->getJsonEComm(route('api.v1.e_comm.customers.get-tax-exemption', ['email' => $model->email]), [
            'Authorization' => 'wrong'
        ]);

        self::assertErrorMsg($res, "Wrong e-comm auth-token", Response::HTTP_UNAUTHORIZED);
    }
}
