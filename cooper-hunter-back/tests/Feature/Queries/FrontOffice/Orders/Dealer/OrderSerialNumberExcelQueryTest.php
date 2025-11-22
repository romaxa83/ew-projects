<?php

namespace Tests\Feature\Queries\FrontOffice\Orders\Dealer;

use App\GraphQL\Queries\FrontOffice\Orders\Dealer as DealerProduct;
use App\Models\Orders\Dealer\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Orders\Dealer\OrderBuilder;
use Tests\Builders\Orders\Dealer\SerialNumberBuilder;
use Tests\TestCase;

class OrderSerialNumberExcelQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = DealerProduct\OrderSerialNumberExcelQuery::NAME;

    protected OrderBuilder $orderBuilder;
    protected SerialNumberBuilder $serialNumberBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->serialNumberBuilder = resolve(SerialNumberBuilder::class);
    }

    /** @test */
    public function get_estimate_url(): void
    {
        Storage::fake('public');
        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $this->serialNumberBuilder->setOrder($order)->create();

        $this->loginAsDealerWithRole();

        $this->postGraphQL([
            'query' => $this->getQueryStr($order->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'type' => 'success',
                        'message' => env('APP_URL') . "/storage/exports/dealer-order/serial-number-{$order->id}.xlsx"
                    ]
                ]
            ])
        ;

        unlink(storage_path("app/public/exports/dealer-order/serial-number-{$order->id}.xlsx"));
    }

    protected function getQueryStr($id): string
    {
        return sprintf(
            '
            {
                %s (id: %s) {
                    type
                    message
                }
            }',
            self::MUTATION,
            $id
        );
    }
}


