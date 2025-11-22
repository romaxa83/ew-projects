<?php

namespace Feature\Http\Api\V1\Customers\EComm;

use App\Enums\Customers\CustomerTaxExemptionStatus;
use App\Enums\Customers\CustomerTaxExemptionType;
use App\Enums\Customers\CustomerType;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerTaxExemption;
use App\Notifications\Users\UserTaxExemptionNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\Tags\TagBuilder;
use Tests\TestCase;

class DeleteTaxExemptionTest extends TestCase
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
    public function success_delete()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Customer */
        $model = $this->customerBuilder
            ->type(CustomerType::EComm)
            ->create();
        CustomerTaxExemption::factory()->for($model)->create();

        $this->deleteJsonEComm(route('api.v1.e_comm.customers.delete-tax-exemption', ['email' => $model->email]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'taxExemption' => null,
                ],
            ]);

        $this->assertDatabaseCount(CustomerTaxExemption::TABLE, 0);
    }

    /** @test */
    public function fail_not_ecomm_customer()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Customer */
        $model = $this->customerBuilder
            ->type(CustomerType::BS)
            ->create();

        $res = $this->deleteJsonEComm(route('api.v1.e_comm.customers.delete-tax-exemption', ['email' => $model->email]));

        self::assertErrorMsg($res, __("exceptions.customer.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->deleteJsonEComm(route('api.v1.e_comm.customers.delete-tax-exemption', ['email' => 'test@gmail.com']));

        self::assertErrorMsg($res, __("exceptions.customer.not_found"), Response::HTTP_NOT_FOUND);
    }


    /** @test */
    public function wrong_token()
    {
        /** @var $model Customer */
        $model = $this->customerBuilder->create();

        $res = $this->deleteJsonEComm(route('api.v1.e_comm.customers.delete-tax-exemption', ['email' => $model->email]), [], [
            'Authorization' => 'wrong'
        ]);

        self::assertErrorMsg($res, "Wrong e-comm auth-token", Response::HTTP_UNAUTHORIZED);
    }
}
