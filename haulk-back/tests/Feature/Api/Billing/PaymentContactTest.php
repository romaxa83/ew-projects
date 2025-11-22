<?php


namespace Api\Billing;


use App\Broadcasting\Events\Subscription\SubscriptionUpdateBroadcast;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PaymentContactTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_company_default_billing_contact(): void
    {
        $companyData = $this->getNewCompanyData();

        $company = $this->createCompany($companyData, config('pricing.plans.regular.slug'));

        $this->assertSame(
            $companyData['email'],
            $company->getPaymentContactData()['email']
        );
    }

    public function test_company_billing_contact(): void
    {
        $companyData = $this->getNewCompanyData();

        $company = $this->createCompany($companyData, config('pricing.plans.regular.slug'));
        $superadmin = $company->getSuperAdmin();
        $name = $this->faker->name;
        $email = $this->faker->email;

        $this->loginAsCarrierSuperAdmin($superadmin);

        Event::fake([SubscriptionUpdateBroadcast::class]);

        $this->postJson(
            route('billing.update-payment-contact'),
            [
                'full_name' => $name,
                'email' => $email,
                'use_accounting_contact' => false,
            ]
        )->assertOk();

        Event::assertDispatched(SubscriptionUpdateBroadcast::class, 1);

        $company->refresh();

        $this->assertSame(
            $email,
            $company->getPaymentContactData()['email']
        );
    }

    public function test_use_accounting_billing_contact(): void
    {
        $companyData = $this->getNewCompanyData();

        $company = $this->createCompany($companyData, config('pricing.plans.regular.slug'));
        $superadmin = $company->getSuperAdmin();
        $name = $this->faker->name;
        $email = $this->faker->email;

        $this->loginAsCarrierSuperAdmin($superadmin);

        Event::fake([SubscriptionUpdateBroadcast::class]);

        $this->postJson(
            route('billing.update-payment-contact'),
            [
                'full_name' => $name,
                'email' => $email,
                'use_accounting_contact' => true,
            ]
        )->assertOk();

        Event::assertDispatched(SubscriptionUpdateBroadcast::class, 1);

        $company->refresh();

        $this->assertSame(
            $companyData['billing_email'],
            $company->getPaymentContactData()['email']
        );
    }
}
