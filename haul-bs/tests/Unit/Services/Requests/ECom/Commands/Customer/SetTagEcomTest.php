<?php

namespace Tests\Unit\Services\Requests\ECom\Commands\Customer;

use App\Foundations\Modules\Media\Traits\TransformFullUrl;
use App\Models\Customers\Customer;
use App\Services\Requests\ECom\Commands\Customer\CustomerSetTagEcomCommand;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\TestCase;

class SetTagEcomTest extends TestCase
{
    use DatabaseTransactions;
    use TransformFullUrl;

    protected CustomerBuilder $customerBuilder;

    public function setUp(): void
    {
        $this->customerBuilder = resolve(CustomerBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function check_prepare_data()
    {
        /** @var $model Customer */
        $model = $this->customerBuilder
            ->create();

        /** @var $command CustomerSetTagEcomCommand */
        $command = resolve(CustomerSetTagEcomCommand::class);

        $res = $command->beforeRequestForData($model);

        $this->assertEquals($res['first_name'], $model->first_name);
        $this->assertEquals($res['last_name'], $model->last_name);
        $this->assertEquals($res['email'], $model->email->getValue());
    }

    /** @test */
    public function check_uri()
    {
        /** @var $command CustomerSetTagEcomCommand */
        $command = resolve(CustomerSetTagEcomCommand::class);
        $this->assertEquals($command->getUri(), config("requests.e_com.paths.customer.set_tag_ecomm"));
    }
}
