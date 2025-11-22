<?php

namespace Feature\Http\Api\V1\Orders\Parts\Catalog;

use App\Enums\Orders\Parts\PaymentTerms;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PaymentTermsTest extends TestCase
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

        $this->getJson(route('api.v1.orders.parts.catalog.payment-terms'))
            ->assertJson([
                'data' => [
                    ['key' => PaymentTerms::Immediately->value, 'title' => PaymentTerms::Immediately->label()],
                    ['key' => PaymentTerms::Day_15->value, 'title' => PaymentTerms::Day_15->label()],
                    ['key' => PaymentTerms::Day_30->value, 'title' => PaymentTerms::Day_30->label()],
                ],
            ])
            ->assertJsonCount(count(PaymentTerms::cases()), 'data')
        ;
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.orders.parts.catalog.payment-terms'));

        self::assertUnauthenticatedMessage($res);
    }
}
