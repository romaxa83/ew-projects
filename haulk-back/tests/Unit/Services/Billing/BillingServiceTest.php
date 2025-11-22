<?php

namespace Tests\Unit\Services\Billing;

use App\Enums\Saas\GPS\DeviceSubscriptionStatus;
use App\Models\Saas\GPS\DevicePayment;
use App\Models\Saas\GPS\DeviceSubscription;
use App\Services\Billing\BillingService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Builders\Saas\GPS\DevicePaymentBuilder;
use Tests\Builders\Saas\GPS\DeviceSubscriptionsBuilder;
use Tests\TestCase;

class BillingServiceTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected DeviceBuilder $deviceBuilder;
    protected DeviceSubscriptionsBuilder $deviceSubscriptionsBuilder;
    protected DevicePaymentBuilder $devicePaymentBuilder;
    protected CompanyBuilder $companyBuilder;


    protected function setUp(): void
    {
        parent::setUp();

        $this->deviceBuilder = resolve(DeviceBuilder::class);
        $this->deviceSubscriptionsBuilder = resolve(DeviceSubscriptionsBuilder::class);
        $this->devicePaymentBuilder = resolve(DevicePaymentBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
    }

    private function generateDateCount(Carbon $firstDay): array
    {
        $firstDay->addDays(random_int(3, 10));
        $result = [];

        foreach (range(0, random_int(3, 15)) as $i) {
            $day = $firstDay->addDay();
            $result[] = [
                'date' => $day->format('Y-m-d'),
                'driver_count' => random_int(0, 20),
                'amount' => random_int(1, 20),
            ];
        }

        return $result;
    }

    private function getBillingService(): BillingService
    {
        return resolve(BillingService::class);
    }

    /** @test */
    public function generate_gps_subscription(): void
    {
        $billingService = $this->getBillingService();

        $date = CarbonImmutable::now();
        $start = $date;
        $end = $date->addDays(2);

        $company = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'));
        $subscription = $company->subscription;
        $subscription->billing_start = $start->startOfDay();
        $subscription->billing_end = $end->endOfDay();
        $subscription->save();

        /** @var $device_subscription DeviceSubscription */
        $device_subscription = $this->deviceSubscriptionsBuilder
            ->currentRate(8)
            ->status(DeviceSubscriptionStatus::ACTIVE())
            ->company($company)->create();

        $d_1 = $this->deviceBuilder->company($company)->create();
        $d_2 = $this->deviceBuilder->company($company)->create();
        $d_3 = $this->deviceBuilder->company($company)->create();

        $d_p_1_1 = $this->devicePaymentBuilder->device($d_1)
            ->date($date)
            ->company($company)->create();
        $d_p_1_2 = $this->devicePaymentBuilder->device($d_1)
            ->date($date->addDay())
            ->company($company)->create();
        $d_p_1_3 = $this->devicePaymentBuilder->device($d_1)
            ->date($date->addDays(2))
            ->company($company)->create();
        $d_p_1_4 = $this->devicePaymentBuilder->device($d_1)
            ->date($date->addDays(3))
            ->company($company)->create();
        $d_p_1_5 = $this->devicePaymentBuilder->device($d_1)
            ->date($date->subDay())
            ->company($company)->create();

        $d_p_2_1 = $this->devicePaymentBuilder->device($d_2)
            ->date($date)
            ->company($company)->create();
        $d_p_2_2 = $this->devicePaymentBuilder->device($d_2)
            ->date($date->addDay())
            ->company($company)->create();
        $d_p_2_3 = $this->devicePaymentBuilder->device($d_2)
            ->date($date->addDays(2))
            ->company($company)->create();

        $d_p_3_1 = $this->devicePaymentBuilder->device($d_3)
            ->date($date)
            ->company($company)->create();
        $d_p_3_2 = $this->devicePaymentBuilder->device($d_3)
            ->date($date->addDay())
            ->company($company)->create();
        $d_p_3_3 = $this->devicePaymentBuilder->device($d_3)
            ->date($date->addDays(2))
            ->company($company)->create();

        $this->assertTrue($company->isGPSEnabled());

        $invoice = $billingService->createMonthlyInvoice($company);

        $this->assertTrue($invoice->has_gps_subscription);

        $this->assertEquals($invoice->drivers_amount, 0);
        $this->assertEquals(
            $invoice->gps_device_amount,
            (3 + 3 + 3) * $device_subscription->current_rate
        );
        $this->assertEquals(
            $invoice->amount,
            (3 + 3 + 3) * $device_subscription->current_rate
        );

        $this->assertEquals($invoice->gps_device_data[0]['days'], 3);
        $this->assertEquals($invoice->gps_device_data[0]['amount'], 3 * $device_subscription->current_rate);
        $this->assertEquals($invoice->gps_device_data[0]['activate'], false);
        $this->assertEquals($invoice->gps_device_data[0]['deactivate'], false);
        $this->assertEquals($invoice->gps_device_data[0]['active_at'], null);
        $this->assertEquals($invoice->gps_device_data[0]['active_till'], null);
        $this->assertEquals($invoice->gps_device_data[0]['name'], $d_1->name);
        $this->assertEquals($invoice->gps_device_data[0]['imei'], $d_1->imei);

        $this->assertEquals($invoice->gps_device_data[1]['days'], 3);
        $this->assertEquals($invoice->gps_device_data[1]['amount'], 3 * $device_subscription->current_rate);
        $this->assertEquals($invoice->gps_device_data[1]['activate'], false);
        $this->assertEquals($invoice->gps_device_data[1]['deactivate'], false);
        $this->assertEquals($invoice->gps_device_data[1]['active_at'], null);
        $this->assertEquals($invoice->gps_device_data[1]['active_till'], null);
        $this->assertEquals($invoice->gps_device_data[1]['name'], $d_2->name);
        $this->assertEquals($invoice->gps_device_data[1]['imei'], $d_2->imei);

        $this->assertEquals($invoice->gps_device_data[2]['days'], 3);
        $this->assertEquals($invoice->gps_device_data[2]['amount'], 3 * $device_subscription->current_rate);
        $this->assertEquals($invoice->gps_device_data[2]['activate'], false);
        $this->assertEquals($invoice->gps_device_data[2]['deactivate'], false);
        $this->assertEquals($invoice->gps_device_data[2]['active_at'], null);
        $this->assertEquals($invoice->gps_device_data[2]['active_till'], null);
        $this->assertEquals($invoice->gps_device_data[2]['name'], $d_3->name);
        $this->assertEquals($invoice->gps_device_data[2]['imei'], $d_3->imei);

        $this->assertEmpty(
            DevicePayment::query()
                ->where('company_id', $company->id)
                ->whereBetween('date', [$start->startOfDay(), $end->endOfDay()])
                ->get()
        );

        $device_subscription->refresh();
        $this->assertEquals($device_subscription->current_rate, 8);
    }

    /** @test */
    public function generate_gps_subscription_one_day_for_device(): void
    {
        $billingService = $this->getBillingService();

        $date = CarbonImmutable::now();
        $start = $date;
        $end = $date->addDays(2);

        $company = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'));
        $subscription = $company->subscription;
        $subscription->billing_start = $start->startOfDay();
        $subscription->billing_end = $end->endOfDay();
        $subscription->save();

        /** @var $device_subscription DeviceSubscription */
        $device_subscription = $this->deviceSubscriptionsBuilder
            ->currentRate(8)
            ->status(DeviceSubscriptionStatus::ACTIVE())
            ->company($company)->create();

        $d_1 = $this->deviceBuilder->company($company)->create();

        $d_p_1 = $this->devicePaymentBuilder
            ->device($d_1)
            ->date($end)
            ->company($company)
            ->create();

        $this->assertTrue($company->isGPSEnabled());

        $invoice = $billingService->createMonthlyInvoice($company);

        $this->assertTrue($invoice->has_gps_subscription);

        $this->assertEquals(
            $invoice->gps_device_amount,
            $device_subscription->current_rate
        );
        $this->assertEquals(
            $invoice->amount,
            $device_subscription->current_rate
        );

        $this->assertEquals($invoice->gps_device_data[0]['days'], 1);
        $this->assertEquals($invoice->gps_device_data[0]['amount'], $device_subscription->current_rate);
        $this->assertEquals($invoice->gps_device_data[0]['activate'], true);
        $this->assertEquals($invoice->gps_device_data[0]['deactivate'], false);
        $this->assertEquals($invoice->gps_device_data[0]['active_at'], null);
        $this->assertEquals($invoice->gps_device_data[0]['active_till'], null);
        $this->assertEquals($invoice->gps_device_data[0]['name'], $d_1->name);
        $this->assertEquals($invoice->gps_device_data[0]['imei'], $d_1->imei);

        $this->assertEmpty(
            DevicePayment::query()
                ->where('company_id', $company->id)
                ->whereBetween('date', [$start->startOfDay(), $end->endOfDay()])
                ->get()
        );

        $device_subscription->refresh();
        $this->assertEquals($device_subscription->current_rate, 8);
    }

    /** @test */
    public function generate_gps_subscription_has_activate_and_deactivate(): void
    {
        $current_rate = 6;
        $next_rate = 6;

        $billingService = $this->getBillingService();

        $date = CarbonImmutable::now();
        $start = $date;
        $end = $date->addDays(2);

        $company = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'));
        $subscription = $company->subscription;
        $subscription->billing_start = $start->startOfDay();
        $subscription->billing_end = $end->endOfDay();
        $subscription->save();

        /** @var $device_subscription DeviceSubscription */
        $device_subscription = $this->deviceSubscriptionsBuilder
            ->currentRate($current_rate)
            ->nextRate($next_rate)
            ->status(DeviceSubscriptionStatus::ACTIVE())
            ->company($company)->create();

        $d_1 = $this->deviceBuilder
            ->activeTillAt($date->addDays(2))
            ->company($company)->create();

        $d_2 = $this->deviceBuilder->company($company)->create();

        $d_p_1_1 = $this->devicePaymentBuilder->device($d_1)
            ->date($date)
            ->company($company)->create();
        $d_p_1_2 = $this->devicePaymentBuilder->device($d_1)
            ->date($date->addDay())
            ->company($company)->create();
        $d_p_1_3 = $this->devicePaymentBuilder->device($d_1)
            ->date($date->addDays(2))
            ->company($company)->create();

        $d_p_2_2 = $this->devicePaymentBuilder->device($d_2)
            ->date($date->addDay())
            ->company($company)->create();
        $d_p_2_3 = $this->devicePaymentBuilder->device($d_2)
            ->date($date->addDays(2))
            ->company($company)->create();


        $this->assertTrue($company->isGPSEnabled());

        $invoice = $billingService->createMonthlyInvoice($company);

        $this->assertTrue($invoice->has_gps_subscription);

        $this->assertEquals(
            $invoice->gps_device_amount,
            (2 + 3) * $device_subscription->current_rate
        );

        $this->assertEquals($invoice->gps_device_data[0]['days'], 3);
        $this->assertEquals($invoice->gps_device_data[0]['amount'], 3 * $device_subscription->current_rate);
        $this->assertEquals($invoice->gps_device_data[0]['activate'], false);
        $this->assertEquals($invoice->gps_device_data[0]['deactivate'], true);
        $this->assertEquals($invoice->gps_device_data[0]['active_at'], null);
        $this->assertNotNull($invoice->gps_device_data[0]['active_till']);
        $this->assertEquals($invoice->gps_device_data[0]['imei'], $d_1->imei);

        $this->assertEquals($invoice->gps_device_data[1]['days'], 2);
        $this->assertEquals($invoice->gps_device_data[1]['amount'], 2 * $device_subscription->current_rate);
        $this->assertEquals($invoice->gps_device_data[1]['activate'], true);
        $this->assertEquals($invoice->gps_device_data[1]['deactivate'], false);
        $this->assertEquals($invoice->gps_device_data[1]['name'], $d_2->name);

        $this->assertEmpty(DevicePayment::query()->where('company_id', $company->id)->get());

        $device_subscription->refresh();
        $this->assertEquals($device_subscription->current_rate, $next_rate);
        $this->assertNull($device_subscription->next_rate);
    }

    /** @test */
    public function generate_gps_subscription_no_data(): void
    {
        $billingService = $this->getBillingService();

        $date = CarbonImmutable::now();
        $start = $date;
        $end = $date->addDays(2);

        $company = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'));
        $subscription = $company->subscription;
        $subscription->billing_start = $start->startOfDay();
        $subscription->billing_end = $end->endOfDay();
        $subscription->save();

        /** @var $device_subscription DeviceSubscription */
        $device_subscription = $this->deviceSubscriptionsBuilder
            ->status(DeviceSubscriptionStatus::ACTIVE())
            ->company($company)->create();

        $d_1 = $this->deviceBuilder
            ->activeTillAt($date->addDays(2))
            ->company($company)->create();

        $d_2 = $this->deviceBuilder->company($company)->create();

        $this->assertTrue($company->isGPSEnabled());

        $invoice = $billingService->createMonthlyInvoice($company);

        $this->assertTrue($invoice->has_gps_subscription);

        $this->assertEquals(
            $invoice->gps_device_amount, 0
        );

        $this->assertEmpty($invoice->gps_device_data);
    }

    /** @test */
    public function generate_gps_subscription_not_subscription(): void
    {
        $billingService = $this->getBillingService();

        $date = CarbonImmutable::now();
        $start = $date;
        $end = $date->addDays(2);

        $company = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'));
        $subscription = $company->subscription;
        $subscription->billing_start = $start->startOfDay();
        $subscription->billing_end = $end->endOfDay();
        $subscription->save();

        $this->assertFalse($company->isGPSEnabled());

        $invoice = $billingService->createMonthlyInvoice($company);

        $invoice->refresh();

        $this->assertFalse($invoice->has_gps_subscription);

        $this->assertEquals(
            $invoice->gps_device_amount, 0
        );

        $this->assertEmpty($invoice->gps_device_data);
    }

    public function test_generate_month_count(): void
    {
        $billingService = resolve(BillingService::class);

        $start = now();
        $end = now()->addMonth();

        $driverHistory = collect(
            $this->generateDateCount($start->clone())
        )->transform(
            function ($el) {
                return (object) $el;
            }
        );

        $monthCount = $billingService->getDailyDriverCountForPeriod($driverHistory, $start, $end);

        $this->assertSame(count($monthCount), $start->daysUntil($end)->count());
    }

    public function test_calculate_planned_price_regular(): void
    {
        $billingService = resolve(BillingService::class);
        $company = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'));

        $subscription = $company->subscription;
        $subscription->billing_start = now()->startOfMonth()->startOfDay();
        $subscription->billing_end = now()->endOfMonth()->endOfDay();
        $subscription->save();

        collect(
            $this->generateDateCount($subscription->billing_start)
        )->map(
            function ($el) use ($company) {
                $this->getBillingService()
                    ->createDriverHistoryRecord($company, $el['driver_count'], $el['date']);
            }
        );

        $this->assertGreaterThan(
            0,
            $billingService->calculateEstimatedPayment($company)['price']
        );
    }

    public function test_calculate_planned_price_trial(): void
    {
        $billingService = resolve(BillingService::class);
        $company = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.trial.slug'));

        $subscription = $company->subscription;
        $subscription->billing_start = now()->startOfMonth()->startOfDay();
        $subscription->billing_end = now()->endOfMonth()->endOfDay();
        $subscription->save();

        collect(
            $this->generateDateCount($subscription->billing_start)
        )->map(
            function ($el) use ($company) {
                $this->getBillingService()
                    ->createDriverHistoryRecord($company, $el['driver_count'], $el['date']);
            }
        );

        $this->assertEquals(
            0,
            $billingService->calculateEstimatedPayment($company)['price']
        );
    }

    public function test_daily_driver_count_for_period_calculation(): void
    {
        $start = new Carbon('2020-08-30');
        $end = new Carbon('2020-09-03');

        $driverHistory = collect(
            [
                (object)[
                    'id' => 1,
                    'date' => '2020-08-30',
                    'driver_count' => 1,
                    'amount' => 8.33,
                ],
            ]
        );

        $this->assertSame(41.65, $this->getPlannedPrice($driverHistory, $start, $end));

        $driverHistory = collect(
            [
                (object)[
                    'id' => 1,
                    'date' => '2020-08-31',
                    'driver_count' => 1,
                    'amount' => 8.33,
                ],
            ]
        );

        $this->assertSame(33.32, $this->getPlannedPrice($driverHistory, $start, $end));

        $driverHistory = collect(
            [
                (object)[
                    'id' => 1,
                    'date' => '2020-08-31',
                    'driver_count' => 1,
                    'amount' => 8.33,
                ],
                (object)[
                    'id' => 1,
                    'date' => '2020-09-02',
                    'driver_count' => 1,
                    'amount' => 8.33,
                ],
            ]
        );

        $this->assertSame(24.99, $this->getPlannedPrice($driverHistory, $start, $end));

        $driverHistory = collect(
            [
                (object)[
                    'id' => 1,
                    'date' => '2020-09-02',
                    'driver_count' => 1,
                    'amount' => 8.33,
                ],
                (object)[
                    'id' => 1,
                    'date' => '2020-09-03',
                    'driver_count' => 1,
                    'amount' => 8.33,
                ],
            ]
        );

        $this->assertSame(16.66, $this->getPlannedPrice($driverHistory, $start, $end));

        $driverHistory = collect(
            [
                (object)[
                    'id' => 1,
                    'date' => '2020-08-30',
                    'driver_count' => 1,
                    'amount' => 8.33,
                ],
                (object)[
                    'id' => 1,
                    'date' => '2020-08-31',
                    'driver_count' => 1,
                    'amount' => 8.33,
                ],
                (object)[
                    'id' => 1,
                    'date' => '2020-09-01',
                    'driver_count' => 1,
                    'amount' => 8.33,
                ],
                (object)[
                    'id' => 1,
                    'date' => '2020-09-02',
                    'driver_count' => 1,
                    'amount' => 8.33,
                ],
                (object)[
                    'id' => 1,
                    'date' => '2020-09-03',
                    'driver_count' => 1,
                    'amount' => 8.33,
                ],
            ]
        );

        $this->assertSame(41.65, $this->getPlannedPrice($driverHistory, $start, $end));

        $this->assertSame(0.0, $this->getPlannedPrice(collect([]), $start, $end));
    }

    private function getPlannedPrice($driverHistory, $start, $end): float
    {
        $billingService = resolve(BillingService::class);

        $plannedPrice = 0;

        foreach ($billingService->getDailyDriverCountForPeriod($driverHistory, $start, $end) as $day) {
            $plannedPrice += $day['amount'];
        }

        return $plannedPrice;
    }
}
