<?php

namespace Feature\Http\Api\V1\Orders\Parts\Catalog;

use App\Enums\Orders\Parts\PaymentMethod;
use App\Enums\Orders\Parts\PaymentTerms;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PaymentMethodsTest extends TestCase
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

        $this->getJson(route('api.v1.orders.parts.catalog.payment-methods'))
            ->assertJson([
                'data' => [
                    ['key' => PaymentMethod::Online->value, 'title' => PaymentMethod::Online->label()],
                    ['key' => PaymentMethod::Zello->value, 'title' => PaymentMethod::Zello->label()],
                    ['key' => PaymentMethod::Venmo->value, 'title' => PaymentMethod::Venmo->label()],
                    ['key' => PaymentMethod::Cashapp->value, 'title' => PaymentMethod::Cashapp->label()],
                    ['key' => PaymentMethod::Check->value, 'title' => PaymentMethod::Check->label()],
                    ['key' => PaymentMethod::Cash->value, 'title' => PaymentMethod::Cash->label()],
                    ['key' => PaymentMethod::ACH->value, 'title' => PaymentMethod::ACH->label()],
                    ['key' => PaymentMethod::Wire->value, 'title' => PaymentMethod::Wire->label()],
                ],
            ])
            ->assertJsonCount(count(PaymentMethod::cases()), 'data')
        ;
    }

    /** @test */
    public function success_list_by_immediately()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.orders.parts.catalog.payment-methods', [
            'payment_terms' => PaymentTerms::Immediately()
        ]))
            ->assertJson([
                'data' => [
                    ['key' => PaymentMethod::Online->value, 'title' => PaymentMethod::Online->label()],
                    ['key' => PaymentMethod::PayPal->value, 'title' => PaymentMethod::PayPal->label()],
                    ['key' => PaymentMethod::Zello->value, 'title' => PaymentMethod::Zello->label()],
                    ['key' => PaymentMethod::Venmo->value, 'title' => PaymentMethod::Venmo->label()],
                    ['key' => PaymentMethod::Cashapp->value, 'title' => PaymentMethod::Cashapp->label()],
                ],
            ])
            ->assertJsonCount(5, 'data')
        ;
    }

    /** @test */
    public function success_list_by_day_15()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.orders.parts.catalog.payment-methods', [
            'payment_terms' => PaymentTerms::Day_15()
        ]))
            ->assertJson([
                'data' => [
                    ['key' => PaymentMethod::ACH->value, 'title' => PaymentMethod::ACH->label()],
                    ['key' => PaymentMethod::Wire->value, 'title' => PaymentMethod::Wire->label()],
                    ['key' => PaymentMethod::Check->value, 'title' => PaymentMethod::Check->label()],
                ],
            ])
            ->assertJsonCount(3, 'data')
        ;
    }

    /** @test */
    public function success_list_by_day_30()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.orders.parts.catalog.payment-methods', [
            'payment_terms' => PaymentTerms::Day_30()
        ]))
            ->assertJson([
                'data' => [
                    ['key' => PaymentMethod::ACH->value, 'title' => PaymentMethod::ACH->label()],
                    ['key' => PaymentMethod::Wire->value, 'title' => PaymentMethod::Wire->label()],
                    ['key' => PaymentMethod::Check->value, 'title' => PaymentMethod::Check->label()],
                ],
            ])
            ->assertJsonCount(3, 'data')
        ;
    }

    /** @test */
    public function success_list_by_add_payment()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.orders.parts.catalog.payment-methods', [
            'for_add_payment' => true
        ]))
            ->assertJson([
                'data' => [
                    ['key' => PaymentMethod::ACH->value, 'title' => PaymentMethod::ACH->label()],
                    ['key' => PaymentMethod::Wire->value, 'title' => PaymentMethod::Wire->label()],
                    ['key' => PaymentMethod::Check->value, 'title' => PaymentMethod::Check->label()],
                    ['key' => PaymentMethod::Cashapp->value, 'title' => PaymentMethod::Cashapp->label()],
                    ['key' => PaymentMethod::Cash->value, 'title' => PaymentMethod::Cash->label()],
                    ['key' => PaymentMethod::Venmo->value, 'title' => PaymentMethod::Venmo->label()],
                    ['key' => PaymentMethod::Zello->value, 'title' => PaymentMethod::Zello->label()],
                ],
            ])
            ->assertJsonCount(7, 'data')
        ;
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.orders.parts.catalog.payment-methods'));

        self::assertUnauthenticatedMessage($res);
    }
}
