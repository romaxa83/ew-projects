<?php

namespace Tests\Feature\Queries\FrontOffice\Orders\Dealer;

use App\GraphQL\Queries\FrontOffice\Orders\Dealer as DealerProduct;
use App\Models\Orders\Dealer\Order;
use App\Models\Orders\Dealer\PackingSlip;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Orders\Dealer\OrderBuilder;
use Tests\Builders\Orders\Dealer\PackingSlipBuilder;
use Tests\Builders\Orders\Dealer\SerialNumberBuilder;
use Tests\TestCase;

class PackingSlipPdfQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = DealerProduct\PackingSlipPdfQuery::NAME;

    protected OrderBuilder $orderBuilder;
    protected PackingSlipBuilder $packingSlipBuilder;
    protected SerialNumberBuilder $serialNumberBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->packingSlipBuilder = resolve(PackingSlipBuilder::class);
        $this->serialNumberBuilder = resolve(SerialNumberBuilder::class);
    }

    /** @test */
    public function generate_pdf(): void
    {
        Storage::fake('public');
        /** @var $order Order */
        $order = $this->orderBuilder->create();
        /** @var $packing_slip PackingSlip */
        $packing_slip = $this->packingSlipBuilder->setOrder($order)->create();

        $this->loginAsDealerWithRole();

        $this->postGraphQL([
            'query' => $this->getQueryStr($packing_slip->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'type' => 'success',
                        'message' => env('APP_URL') . "/storage/dealer-order-pdf/Order-{$order->id}-packing-slim-{$packing_slip->id}-pdf.pdf"
                    ]
                ]
            ])
        ;

        unlink(storage_path("app/public/dealer-order-pdf/Order-{$order->id}-packing-slim-{$packing_slip->id}-pdf.pdf"));
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


