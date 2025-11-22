<?php

namespace Tests\Feature\Queries\FrontOffice\Orders\Dealer;

use App\GraphQL\Queries\FrontOffice\Orders\Dealer as DealerProduct;
use App\Models\Companies\Company;
use App\Models\Dealers\Dealer;
use App\Models\Orders\Dealer\Order;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Catalog\ProductBuilder;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Company\CompanyPriceBuilder;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\Builders\Orders\Dealer\ItemBuilder;
use Tests\Builders\Orders\Dealer\OrderBuilder;
use Tests\TestCase;

class ProductsExcelQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = DealerProduct\ProductsExcelQuery::NAME;

    protected ProductBuilder $productBuilder;
    protected DealerBuilder $dealerBuilder;
    protected OrderBuilder $orderBuilder;
    protected ItemBuilder $itemBuilder;
    protected CompanyBuilder $companyBuilder;
    protected CompanyPriceBuilder $companyPriceBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->productBuilder = resolve(ProductBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->companyPriceBuilder = resolve(CompanyPriceBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->itemBuilder = resolve(ItemBuilder::class);
        $this->dealerBuilder = resolve(DealerBuilder::class);
    }

    /** @test */
    public function list_product(): void
    {
        $now = Carbon::now();
        CarbonImmutable::setTestNow($now);

        Storage::fake('public');
        /** @var $order Order */
        $order = $this->orderBuilder->create();
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        /** @var $dealer Dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();

        $product_1 = $this->productBuilder->create();

        $this->itemBuilder->setOrder($order)->setProduct($product_1)->create();

        $this->companyPriceBuilder->setProduct($product_1)
            ->setCompany($company)->create();

        $this->loginAsDealerWithRole($dealer);

        $this->postGraphQL([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'type' => 'success',
                        'message' => env('APP_URL') . "/storage/exports/order-dealer/products-{$now->timestamp}.xlsx"
                    ]
                ]
            ])
        ;
    }

    protected function getQueryStr(): string
    {
        return sprintf(
            '
            {
                %s {
                    type
                    message
                }
            }',
            self::MUTATION
        );
    }
}
