<?php

namespace Tests\Unit\Services\Saas\GPS\Device\DevicePaymentService;

use App\Enums\Format\DateTimeEnum;
use App\Enums\Saas\GPS\DeviceHistoryContext;
use App\Enums\Saas\GPS\DeviceStatus;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\Device;
use App\Models\Saas\GPS\DeviceSubscription;
use App\Services\Saas\GPS\Devices\DevicePaymentService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Builders\Saas\GPS\DevicePaymentBuilder;
use Tests\Builders\Saas\GPS\DeviceSubscriptionsBuilder;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;

    protected DeviceBuilder $deviceBuilder;
    protected CompanyBuilder $companyBuilder;
    protected DeviceSubscriptionsBuilder $deviceSubscriptionsBuilder;
    protected DevicePaymentBuilder $devicePaymentBuilder;

    protected DevicePaymentService $service;


    protected function setUp(): void
    {
        parent::setUp();

        $this->deviceBuilder = resolve(DeviceBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->deviceSubscriptionsBuilder = resolve(DeviceSubscriptionsBuilder::class);
        $this->devicePaymentBuilder = resolve(DevicePaymentBuilder::class);

        $this->service = resolve(DevicePaymentService::class);
    }

    /** @test */
    public function create(): void
    {
        $rate = 5;
        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $company Company */
        $company = $this->companyBuilder->regularPlan($this->companyBuilder->create());

        /** @var $subscription DeviceSubscription */
        $subscription = $this->deviceSubscriptionsBuilder
            ->currentRate($rate)
            ->company($company)->create();

        /** @var $d_1 Device */
        $d_1 = $this->deviceBuilder->company($company)
            ->status(DeviceStatus::ACTIVE())->create();
        $d_2 = $this->deviceBuilder->company($company)
            ->status(DeviceStatus::ACTIVE())
            ->activeTillAt($date->addDay())
            ->create();

        $this->assertEmpty($d_1->paymentItems);
        $this->assertEmpty($d_2->paymentItems);

        $this->service->create($company);

        $d_1->refresh();
        $d_2->refresh();

        $this->assertCount(1, $d_1->paymentItems);
        $this->assertEquals($d_1->paymentItems[0]->company_id, $company->id);
        $this->assertFalse($d_1->paymentItems[0]->deactivate);
        $this->assertEquals($d_1->paymentItems[0]->amount, round($rate/3, 2));
        $this->assertEquals($d_1->paymentItems[0]->date->format(DateTimeEnum::DATE), $date->format(DateTimeEnum::DATE));

        $this->assertEquals($d_1->histories[0]->context, DeviceHistoryContext::PAYMENT_REC());


        $this->assertCount(1, $d_2->paymentItems);
        $this->assertEquals($d_2->paymentItems[0]->company_id, $company->id);
        $this->assertTrue($d_2->paymentItems[0]->deactivate);
        $this->assertEquals($d_2->paymentItems[0]->amount, round($rate/3, 2));
        $this->assertEquals($d_2->paymentItems[0]->date->format(DateTimeEnum::DATE), $date->format(DateTimeEnum::DATE));

        $this->assertEquals($d_2->histories[0]->context, DeviceHistoryContext::PAYMENT_REC());
    }

    /** @test */
    public function not_create_if_exists(): void
    {
        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $company Company */
        $company = $this->companyBuilder->regularPlan($this->companyBuilder->create());

        /** @var $subscription DeviceSubscription */
        $subscription = $this->deviceSubscriptionsBuilder
            ->company($company)->create();

        /** @var $d_1 Device */
        $d_1 = $this->deviceBuilder->company($company)
            ->status(DeviceStatus::ACTIVE())->create();
        $d_2 = $this->deviceBuilder->company($company)
            ->status(DeviceStatus::ACTIVE())
            ->activeTillAt($date->addDay())
            ->create();
        $d_3 = $this->deviceBuilder->company($company)
            ->status(DeviceStatus::INACTIVE())
            ->create();
        $d_4 = $this->deviceBuilder->company($company)
            ->status(DeviceStatus::DELETED())
            ->create();

        $p_1 = $this->devicePaymentBuilder->device($d_1)
            ->company($company)
            ->create();

        $this->assertCount(1, $d_1->paymentItems);
        $this->assertCount(0, $d_2->paymentItems);
        $this->assertCount(0, $d_3->paymentItems);
        $this->assertCount(0, $d_4->paymentItems);

        $this->service->create($company);

        $d_1->refresh();
        $d_2->refresh();
        $d_3->refresh();
        $d_4->refresh();

        $this->assertCount(1, $d_1->paymentItems);
        $this->assertCount(1, $d_2->paymentItems);
        $this->assertCount(0, $d_3->paymentItems);
        $this->assertCount(0, $d_4->paymentItems);
    }

    /** @test */
    public function create_yet(): void
    {
        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $company Company */
        $company = $this->companyBuilder->regularPlan($this->companyBuilder->create());

        /** @var $subscription DeviceSubscription */
        $subscription = $this->deviceSubscriptionsBuilder
            ->company($company)->create();

        /** @var $d_1 Device */
        $d_1 = $this->deviceBuilder->company($company)
            ->status(DeviceStatus::ACTIVE())->create();


        $p_1 = $this->devicePaymentBuilder->device($d_1)
            ->company($company)
            ->date($date->subDay())
            ->create();

        $this->assertCount(1, $d_1->paymentItems);

        $this->service->create($company);

        $d_1->refresh();

        $this->assertCount(2, $d_1->paymentItems);
    }
}

