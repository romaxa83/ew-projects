<?php

namespace Feature\Http\Api\V1\Inventories\Inventory\Transaction;

use App\Enums\Inventories\Transaction\PaymentMethod;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PaymentMethodTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success_list()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.inventories.transactions.payment-method'))
            ->assertJson([
                'data' => [
                    ['key' => PaymentMethod::Cash->value, 'title' => PaymentMethod::Cash->label()],
                    ['key' => PaymentMethod::Check->value, 'title' => PaymentMethod::Check->label()],
                    ['key' => PaymentMethod::MoneyOrder->value, 'title' => PaymentMethod::MoneyOrder->label()],
                    ['key' => PaymentMethod::QuickPay->value, 'title' => PaymentMethod::QuickPay->label()],
                    ['key' => PaymentMethod::Paypal->value, 'title' => PaymentMethod::Paypal->label()],
                    ['key' => PaymentMethod::CashApp->value, 'title' => PaymentMethod::CashApp->label()],
                    ['key' => PaymentMethod::Venmo->value, 'title' => PaymentMethod::Venmo->label()],
                    ['key' => PaymentMethod::Zelle->value, 'title' => PaymentMethod::Zelle->label()],
                    ['key' => PaymentMethod::CreditCard->value, 'title' => PaymentMethod::CreditCard->label()],
                    ['key' => PaymentMethod::Card->value, 'title' => PaymentMethod::Card->label()],
                    ['key' => PaymentMethod::WireTransfer->value, 'title' => PaymentMethod::WireTransfer->label()],
                ],
            ])
            ->assertJsonCount(count(PaymentMethod::cases()), 'data')
        ;
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.inventories.transactions.payment-method'));

        self::assertUnauthenticatedMessage($res);
    }
}
