<?php


namespace Api\Billing;


use App\Broadcasting\Events\Subscription\SubscriptionUpdateBroadcast;
use App\Models\Users\User;
use App\Services\Permissions\Payments\PaymentProviderInterface;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\Helpers\Traits\Billing\BillingMethodsHelper;
use Tests\TestCase;

class PaymentMethodTest extends TestCase
{
    use DatabaseTransactions;
    use BillingMethodsHelper;

    public function test_create_update_payment_method(): void
    {
        $user = User::factory()->create();
        $user->assignRole(User::SUPERADMIN_ROLE);

        $this->loginAsCarrierSuperAdmin($user);

        $service = resolve(PaymentProviderInterface::class);

        Event::fake([SubscriptionUpdateBroadcast::class]);

        // save
        $this->postJson(
            route('billing.update-payment-method'),
            $this->getRandomPaymentMethodData()
        )
            ->assertOk();

        $paymentData = $user->getCompany()->getPaymentMethodData();

        $this->assertNotNull($paymentData);

        $this->assertNotEmpty(
            $service->makePayment($paymentData, 100)
        );

        // update
        $this->postJson(
            route('billing.update-payment-method'),
            $this->getRandomPaymentMethodData()
        )
            ->assertOk();

        Event::assertDispatched(SubscriptionUpdateBroadcast::class, 2);

        $paymentData = $user->getCompany()->getPaymentMethodData();

        $this->assertNotNull($paymentData);

        $this->assertNotEmpty(
            $service->makePayment($paymentData, 100)
        );
    }
}
