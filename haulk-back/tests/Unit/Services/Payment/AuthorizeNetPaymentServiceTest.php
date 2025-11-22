<?php


namespace Services\Payment;


use App\Dto\Payments\AuthorizeNet\AuthorizeNetMemberProfileDto;
use App\Dto\Payments\PaymentMethodRequestDto;
use App\Services\Permissions\Payments\AuthorizeNetPaymentService;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthorizeNetPaymentServiceTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /**
     * @throws Exception
     */
    public function test_create_payment_profile(): void
    {
        $paymentData = $this->createPaymentMethod();

        $this->assertNotEmpty($paymentData);
        $this->assertNotEmpty($paymentData->getCardNumber());
        $this->assertNotEmpty($paymentData->getId());
    }

    public function test_create_profile_fails_cvv(): void
    {
        $this->expectException(Exception::class);
        $this->createPaymentMethod(null, AuthorizeNetPaymentService::AUTHORIZE_NET_CVV_FAIL);
    }

    /**
     * @throws Exception
     */
    public function test_make_payment_with_profile(): void
    {
        $service = resolve(AuthorizeNetPaymentService::class);

        $paymentData = $this->createPaymentMethod();

        $this->assertNotEmpty($paymentData);

        $this->assertNotEmpty(
            $service->makePayment($paymentData, 100)
        );
    }

    /**
     * @throws Exception
     */
    public function test_profile_created_but_payment_fails(): void
    {
        $paymentData = $this->createPaymentMethod('46282');

        $this->assertNotEmpty($paymentData);

        $service = resolve(AuthorizeNetPaymentService::class);

        $this->expectException(Exception::class);
        $service->makePayment($paymentData, 100);
    }

    /**
     * @param string|null $zipCode
     * @param int|null $cvv
     * @return AuthorizeNetMemberProfileDto
     * @throws Exception
     */
    private function createPaymentMethod(?string $zipCode = null, ?int $cvv = null): AuthorizeNetMemberProfileDto
    {
        $service = resolve(AuthorizeNetPaymentService::class);

        return $service->storePaymentData(
            new PaymentMethodRequestDto(
                $this->faker->randomNumber(),
                $this->faker->email,
                $this->faker->firstNameMale,
                $this->faker->lastName,
                mb_substr($this->faker->address, 0, 60),
                $this->faker->city,
                $this->faker->state,
                $zipCode ?? $this->faker->postcode,
                'USA',
                '4111111111111111',
                $this->faker->month,
                now()->addYear()->format('Y'),
                $cvv ?? AuthorizeNetPaymentService::AUTHORIZE_NET_CVV_SUCCESS
            )
        );
    }
}
