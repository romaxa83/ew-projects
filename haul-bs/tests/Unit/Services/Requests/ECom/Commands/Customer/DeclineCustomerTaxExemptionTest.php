<?php

namespace Tests\Unit\Services\Requests\ECom\Commands\Customer;

use App\Foundations\Modules\Media\Traits\TransformFullUrl;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerTaxExemption;
use App\Services\Requests\ECom\Commands\Customer\CustomerTaxExemptionDeclineCommand;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DeclineCustomerTaxExemptionTest extends TestCase
{
    use DatabaseTransactions;
    use TransformFullUrl;

    /** @test */
    public function check_prepare_data()
    {
        /** @var $model CustomerTaxExemption */
        $model = CustomerTaxExemption::factory()->for(Customer::factory())->create();

        /** @var $command CustomerTaxExemptionDeclineCommand */
        $command = resolve(CustomerTaxExemptionDeclineCommand::class);

        $res = $command->beforeRequestForData($model);

        $this->assertEquals($res['email'], $model->customer->email);
    }


    /** @test */
    public function check_uri()
    {
        $model = CustomerTaxExemption::factory()->for(Customer::factory())->create();

        /** @var $command CustomerTaxExemptionDeclineCommand */
        $command = resolve(CustomerTaxExemptionDeclineCommand::class);

        $res = $command->beforeRequestForData($model);

        $this->assertEquals($command->getUri($res), str_replace('{email}', $model->customer->email, config("requests.e_com.paths.customer_tax_exemption.decline")));
    }
}
