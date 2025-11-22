<?php

namespace Tests\Unit\Models\Billing;

use App\Models\Billing\Invoice;
use App\Models\Saas\Company\Company;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Billing\InvoiceBuilder;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use DatabaseTransactions;

    protected CompanyBuilder $companyBuilder;
    protected InvoiceBuilder $invoiceBuilder;


    protected function setUp(): void
    {
        parent::setUp();

        $this->invoiceBuilder = resolve(InvoiceBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
    }

    /** @test */
    public function format_gps_device_data_for_pdf(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();

        $data = [
            [
                "days" => 3,
                "amount" => 6
            ],
            [
                "days" => 10,
                "amount" => 20
            ],
            [
                "days" => 10,
                "amount" => 20
            ],
            [
                "days" => 3,
                "amount" => 6
            ],
            [
                "days" => 10,
                "amount" => 20
            ],
            [
                "days" => 5,
                "amount" => 10
            ]
        ];

        /** @var $model Invoice */
        $model = $this->invoiceBuilder
            ->company($company)
            ->hasGpsSubscription()
            ->gpsSubscriptionData($data)
            ->create();

        $collection = $model->formatGpsDeviceDataForPdf();

        $this->assertEquals($collection[0], [
            'amount' => 60,
            'count' => 3,
            'days' => 10,
        ]);
        $this->assertEquals($collection[1], [
            'amount' => 10,
            'count' => 1,
            'days' => 5,
        ]);
        $this->assertEquals($collection[2], [
            'amount' => 12,
            'count' => 2,
            'days' => 3,
        ]);
    }

    /** @test */
    public function format_gps_device_data_for_pdf_empty(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();

        /** @var $model Invoice */
        $model = $this->invoiceBuilder
            ->company($company)
            ->hasGpsSubscription()
            ->create();

        $collection = $model->formatGpsDeviceDataForPdf();

        $this->assertEmpty($collection);
    }
}

