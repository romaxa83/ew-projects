<?php

namespace Tests\Unit\Dto\Payment;

use App\Dto\Payments\PaymentCardDto;
use Tests\TestCase;

class PaymentCardDtoTest extends TestCase
{
    /** @test */
    public function success_without_billing_address()
    {
        $dto = PaymentCardDto::byArgs([
            "payment_card" => [
                "type" => "Visa",
                "name" => "Dr. Winnifred Trantow V",
                "number" => "4485100135006686",
                "cvc" => "371",
                "expiration_date" => "12/24"
            ]
        ]);

        $this->assertNull($dto->billingAddress);
    }

    /** @test */
    public function success_last_numeric_code()
    {
        $dto = PaymentCardDto::byArgs([
            "payment_card" => [
                "type" => "Visa",
                "name" => "Dr. Winnifred Trantow V",
                "number" => "4485100135006686",
                "cvc" => "371",
                "expiration_date" => "12/24"
            ]
        ]);

        $this->assertEquals("6686", $dto->lastFourNumericForCode());
    }

    /** @test */
    public function success_hash()
    {
        $dto = PaymentCardDto::byArgs([
            "payment_card" => [
                "type" => "Master Card",
                "name" => "Dr. Winnifred Trantow V",
                "number" => "4485 1001 3500 6686",
                "cvc" => "371",
                "expiration_date" => "12/24"
            ]
        ]);

        $this->assertEquals($dto->hash(), md5('mastercard448510013500668637112/24'));
    }
}

