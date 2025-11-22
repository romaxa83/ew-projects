<?php

namespace Feature\Http\Api\V1\Orders\Parts\Catalog;

use App\Enums\Orders\Parts\OrderSource;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OrderSourceTest extends TestCase
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

        $this->getJson(route('api.v1.orders.parts.catalog.source'))
            ->assertJson([
                'data' => [
                    ['key' => OrderSource::BS->value, 'title' => OrderSource::BS->label()],
                    ['key' => OrderSource::Amazon->value, 'title' => OrderSource::Amazon->label()],
                    ['key' => OrderSource::Haulk_Depot->value, 'title' => OrderSource::Haulk_Depot->label()],
                ],
            ])
            ->assertJsonCount(count(OrderSource::cases()), 'data')
        ;
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.orders.parts.catalog.source'));

        self::assertUnauthenticatedMessage($res);
    }
}
