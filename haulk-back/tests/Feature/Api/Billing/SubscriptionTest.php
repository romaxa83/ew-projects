<?php

namespace Api\Billing;

use App\Broadcasting\Events\Subscription\SubscriptionUpdateBroadcast;
use App\Console\Commands\Billing\CreateInvoices;
use App\Enums\Saas\GPS\DeviceSubscriptionStatus;
use App\Notifications\Billing\RenewSubscribe;
use App\Notifications\Billing\SuccessUnsubscribe;
use App\Services\Billing\BillingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Builders\Saas\GPS\DevicePaymentBuilder;
use Tests\Builders\Saas\GPS\DeviceSubscriptionsBuilder;
use Tests\Helpers\Traits\Billing\BillingMethodsHelper;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use DatabaseTransactions;
    use BillingMethodsHelper;
    use UserFactoryHelper;

    protected DeviceBuilder $deviceBuilder;
    protected DeviceSubscriptionsBuilder $deviceSubscriptionsBuilder;
    protected DevicePaymentBuilder $devicePaymentBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deviceBuilder = resolve(DeviceBuilder::class);
        $this->deviceSubscriptionsBuilder = resolve(DeviceSubscriptionsBuilder::class);
        $this->devicePaymentBuilder = resolve(DevicePaymentBuilder::class);
    }

    public function test_restrict_access_with_unpaid_invoice_after_three_days(): void
    {
        $companyData = $this->getNewCompanyData();

        $company = $this->createCompany($companyData, config('pricing.plans.regular.slug'), true);
        $superadmin = $company->getSuperAdmin();

        $this->assertTrue($company->isSubscriptionActive());

        $subscription = $company->subscription;
        $billingStart = now()
            ->subDay()
            ->subMonth()
            ->startOfDay();

        $subscription->billing_start = $billingStart;
        $subscription->billing_end = now()
            ->subDay()
            ->endOfDay();
        $subscription->save();

        collect(
            $this->generateDateCount($billingStart, now())
        )->map(
            function ($el) use ($company) {
                $this->getBillingService()
                    ->createDriverHistoryRecord($company, $el['driver_count'], $el['date']);
            }
        );

        $this->artisan(CreateInvoices::class);

        $company->refresh();

        $this->assertTrue($company->hasUnpaidInvoices());

        $invoice = $company->invoices->first();

        $invoice->attempt = 2;
        $invoice->save();

        $this->loginAsCarrierSuperAdmin($superadmin);

        $this->postJson(
            route('question-answer.store'),
            [
                'question_en' => 'question_en',
                'answer_en' => 'answer_en',
            ]
        )
            ->assertCreated();

        $invoice->attempt = 3;
        $invoice->save();

        $company->refresh();

        $this->postJson(
            route('question-answer.store'),
            [
                'question_en' => 'question_en',
                'answer_en' => 'answer_en',
            ]
        )
            ->assertStatus(Response::HTTP_PAYMENT_REQUIRED);

        $this->assertTrue($company->paymentAttemptsCountExhausted());

        $billingService = resolve(BillingService::class);
        $this->assertEquals(0, $billingService->calculateDriverDailyPrice($company));
    }

    public function test_unsubscribe_trial(): void
    {
        $companyData = $this->getNewCompanyData();

        $company = $this->createCompany($companyData, config('pricing.plans.trial.slug'));
        $superadmin = $company->getSuperAdmin();

        $this->assertTrue($company->isSubscriptionActive());

        $this->loginAsCarrierSuperAdmin($superadmin);

        Event::fake();
        Notification::fake();

        $this->putJson(route('billing.unsubscribe'))
            ->assertOk();

        Event::assertDispatched(SubscriptionUpdateBroadcast::class);
        Notification::assertSentTo(new AnonymousNotifiable(), SuccessUnsubscribe::class);
    }

    public function test_unsubscribe_regular_no_unpaid(): void
    {
        $companyData = $this->getNewCompanyData();

        $company = $this->createCompany($companyData, config('pricing.plans.regular.slug'), true);
        $superadmin = $company->getSuperAdmin();

        $this->assertTrue($company->isSubscriptionActive());
        $this->assertFalse($company->hasUnpaidInvoices());

        $this->loginAsCarrierSuperAdmin($superadmin);

        Event::fake();
        Notification::fake();

        $this->putJson(route('billing.unsubscribe'))
            ->assertOk();

        Event::assertDispatched(SubscriptionUpdateBroadcast::class);
        Notification::assertSentTo(new AnonymousNotifiable(), SuccessUnsubscribe::class);
    }

    public function test_unsubscribe(): void
    {
        $companyData = $this->getNewCompanyData();

        $company = $this->createCompany($companyData, config('pricing.plans.regular.slug'));

        $deviceSubscription = $this->deviceSubscriptionsBuilder
            ->company($company)->status(DeviceSubscriptionStatus::ACTIVE())->create();

        $d_1 = $this->deviceBuilder->company($company)->create();

        $d_p_1 = $this->devicePaymentBuilder->device($d_1)->company($company)->create();

        $subscription = $company->subscription;
        $billingStart = now()
            ->subDays(5)
            ->startOfDay();

        $subscription->billing_start = $billingStart;
        $subscription->billing_end = now()
            ->subDays(5)
            ->addMonth()
            ->endOfDay();
        $subscription->save();

        $company->refresh();

        $this->assertTrue($company->isSubscriptionActive());

        collect(
            $this->generateDateCount($billingStart, now())
        )->map(
            function ($el) use ($company) {
                $this->getBillingService()
                    ->createDriverHistoryRecord($company, $el['driver_count'], $el['date']);
            }
        );

        $this->loginAsCarrierSuperAdmin($company->getSuperAdmin());

        Event::fake();
        Notification::fake();

        $this->postJson(
            route('billing.update-payment-method'),
            $this->getRandomPaymentMethodData()
        )
            ->assertOk();

        Event::assertDispatched(SubscriptionUpdateBroadcast::class);
        Notification::assertNothingSent();

        $this->putJson(route('billing.unsubscribe'))
            ->assertOk();

        Notification::assertSentTo(new AnonymousNotifiable(), SuccessUnsubscribe::class);

        Event::assertDispatched(SubscriptionUpdateBroadcast::class);

        $company->refresh();

        $this->assertFalse($company->isSubscriptionActive());
        $this->assertFalse($company->hasUnpaidInvoices());
    }

    public function test_subscribe_active_subscription(): void
    {
        $companyData = $this->getNewCompanyData();

        $company = $this->createCompany($companyData, config('pricing.plans.regular.slug'), true);
        $superadmin = $company->getSuperAdmin();

        $this->assertTrue($company->isSubscriptionActive());

        $this->loginAsCarrierSuperAdmin($superadmin);

        Event::fake();

        $this->putJson(route('billing.subscribe'))
            ->assertStatus(Response::HTTP_FORBIDDEN);

        Event::assertNotDispatched(SubscriptionUpdateBroadcast::class);
    }

    public function test_subscribe_inactive_subscription(): void
    {
        $companyData = $this->getNewCompanyData();

        $company = $this->createCompany($companyData, config('pricing.plans.regular.slug'));
        $superadmin = $company->getSuperAdmin();

        $this->assertTrue($company->isSubscriptionActive());
        $this->assertFalse($company->hasUnpaidInvoices());

        $this->loginAsCarrierSuperAdmin($superadmin);

        Event::fake();
        Notification::fake();

        $this->putJson(route('billing.unsubscribe'))
            ->assertOk();

        Notification::assertSentTo(new AnonymousNotifiable(), SuccessUnsubscribe::class);

        //Event::assertDispatched(SubscriptionUpdateBroadcast::class);

        $company->refresh();

        $this->assertFalse($company->isSubscriptionActive());
        $this->assertFalse($company->hasPaymentMethod());

        $this->putJson(route('billing.subscribe'))
            ->assertStatus(Response::HTTP_PAYMENT_REQUIRED);

        //Event::assertNotDispatched(SubscriptionUpdateBroadcast::class);

        $this->postJson(
            route('billing.update-payment-method'),
            $this->getRandomPaymentMethodData()
        )
            ->assertOk();

        Event::assertDispatched(SubscriptionUpdateBroadcast::class);

        Notification::assertSentTo(new AnonymousNotifiable(), RenewSubscribe::class);

        $company->refresh();

        $this->assertTrue($company->isSubscriptionActive());
        $this->assertTrue($company->hasPaymentMethod());
    }

    public function test_driver_price_after_trial_expire_and_card_added(): void
    {
        $companyData = $this->getNewCompanyData();

        $company = $this->createCompany($companyData, config('pricing.plans.trial.slug'));
        $superadmin = $company->getSuperAdmin();
        $this->driverFactory(['carrier_id' => $company->id]);

        $this->assertTrue($company->isSubscriptionActive());
        $this->assertTrue($company->isInTrialPeriod());
        $this->assertEquals(0, $company->invoices->count());

        $subscription = $company->subscription;
        $subscription->billing_start = now()->subDays(10)->startOfDay();
        $subscription->billing_end = now()->subDays(5)->endOfDay();
        $subscription->save();

        $this->getBillingService()
           ->createDriverHistoryRecord($company, 1, now()->subDays(5)->format('Y-m-d'));

        $this->artisan(CreateInvoices::class);

        $company->refresh();

        $this->assertEquals(1, $company->invoices->count());
        $this->assertFalse($company->isSubscriptionActive());
        $this->assertTrue($company->isTrialExpired());

        $this->loginAsCarrierSuperAdmin($superadmin);

        $this->getJson(route('billing.billing-info'))
            ->assertJsonPath('data.estimated_payment.price', 0)
            ->assertJsonPath('data.estimated_payment.driver_count', 0);

        $this->postJson(
            route('billing.update-payment-method'),
            $this->getRandomPaymentMethodData()
        )
            ->assertOk();

        $company->refresh();

        $this->assertTrue($company->isSubscriptionActive());
        $this->assertFalse($company->isInTrialPeriod());
        $this->assertEquals(config('pricing.plans.regular.id'), $company->subscription->pricing_plan_id);

        $response = $this->getJson(route('billing.billing-info'))
            ->json('data.estimated_payment');

        $this->assertGreaterThan(0, $response['price']);
        $this->assertEquals(1, $response['driver_count']);
    }

    private function generateDateCount(Carbon $start, Carbon $end): array
    {
        $period = $start->toPeriod($end);
        $result = [];

        foreach ($period as $day) {
            $result[] = [
                'date' => $day->format('Y-m-d'),
                'driver_count' => random_int(0, 20),
            ];
        }

        return $result;
    }

    private function getBillingService(): BillingService
    {
        return resolve(BillingService::class);
    }
}
