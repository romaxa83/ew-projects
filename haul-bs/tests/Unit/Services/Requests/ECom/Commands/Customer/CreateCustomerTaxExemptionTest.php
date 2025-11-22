<?php

namespace Tests\Unit\Services\Requests\ECom\Commands\Customer;

use App\Foundations\Modules\Media\Traits\TransformFullUrl;
use App\Models\Customers\CustomerTaxExemption;
use App\Services\Requests\ECom\Commands\Customer\CustomerTaxExemptionCreateCommand;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Customers\CustomerTaxExemptionBuilder;
use Tests\TestCase;

class CreateCustomerTaxExemptionTest extends TestCase
{
    use DatabaseTransactions;
    use TransformFullUrl;

    protected CustomerTaxExemptionBuilder $builder;

    public function setUp(): void
    {
        $this->builder = resolve(CustomerTaxExemptionBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function check_prepare_data()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        /** @var $model CustomerTaxExemption */
        $model = $this->builder->file(UploadedFile::fake()->image('img.png'))->create();

        /** @var $command CustomerTaxExemptionCreateCommand */
        $command = resolve(CustomerTaxExemptionCreateCommand::class);

        $res = $command->beforeRequestForData($model);

        $this->assertEquals($res['date_active_to'], $model->date_active_to->format('m-d-Y'));
        $this->assertEquals($res['link'], $this->fullUrl($model->file));
    }


    /** @test */
    public function check_uri()
    {
        /** @var $model CustomerTaxExemption */
        $model = $this->builder->file(UploadedFile::fake()->image('img.png'))->create();

        /** @var $command CustomerTaxExemptionCreateCommand */
        $command = resolve(CustomerTaxExemptionCreateCommand::class);

        $res = $command->beforeRequestForData($model);

        $this->assertEquals($command->getUri($res), str_replace('{email}', $model->customer->email, config("requests.e_com.paths.customer_tax_exemption.create")));
    }
}
