<?php

namespace Tests\Feature\Queries\FrontOffice\Orders\Dealer;

use App\Enums\Orders\Dealer\OrderStatus;
use App\GraphQL\Queries\FrontOffice\Orders\Dealer\ReportExcelQuery;
use App\Models\Companies\Corporation;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\Builders\Catalog\ProductBuilder;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Company\CompanyShippingAddressBuilder;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\Builders\Orders\Dealer\ItemBuilder;
use Tests\Builders\Orders\Dealer\OrderBuilder;
use Tests\TestCase;

class ReportQueryExcelTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = ReportExcelQuery::NAME;

    protected DealerBuilder $dealerBuilder;
    protected CompanyShippingAddressBuilder $addressBuilder;
    protected OrderBuilder $orderBuilder;
    protected CompanyBuilder $companyBuilder;
    protected ProductBuilder $productBuilder;
    protected ItemBuilder $itemBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->dealerBuilder = resolve(DealerBuilder::class);
        $this->addressBuilder = resolve(CompanyShippingAddressBuilder::class);
        $this->productBuilder = resolve(ProductBuilder::class);
        $this->itemBuilder = resolve(ItemBuilder::class);
    }

    /** @test */
    public function get_report_url(): void
    {
        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow($now);

        $corp = Corporation::factory()->create();

        $company_1 = $this->companyBuilder->setCorporation($corp)->create();
        $company_2 = $this->companyBuilder->setCorporation($corp)->create();
        $company_3 = $this->companyBuilder->create();

        $address_1_1 = $this->addressBuilder->setCompany($company_1)->create();
        $address_1_2 = $this->addressBuilder->setCompany($company_1)->create();
        $address_2_1 = $this->addressBuilder->setCompany($company_2)->create();
        $address_3_1 = $this->addressBuilder->setCompany($company_3)->create();

        $dealer_1 = $this->dealerBuilder->setCompany($company_1)
            ->setData(['is_main' => true])->create();
        $dealer_2 = $this->dealerBuilder->setCompany($company_2)->create();
        $dealer_3 = $this->dealerBuilder->setCompany($company_3)->create();

        $this->loginAsDealerWithRole($dealer_1);

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();

        $order_1 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_1)->setData(['approved_at' => CarbonImmutable::now()])
            ->setShippingAddress($address_1_1)->create();
        $order_2 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_1)->setData(['approved_at' => CarbonImmutable::now()->addDay()])
            ->setShippingAddress($address_1_1)->create();
        $order_3 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_1)->setData(['approved_at' => CarbonImmutable::now()->addHour()])
            ->setShippingAddress($address_1_2)->create();
        $order_4 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_2)->setData(['approved_at' => CarbonImmutable::now()->subHour()])
            ->setShippingAddress($address_2_1)->create();
        $order_5 = $this->orderBuilder->setStatus(OrderStatus::SHIPPED)
            ->setDealer($dealer_3)->setData(['approved_at' => CarbonImmutable::now()->subHour()])
            ->setShippingAddress($address_3_1)->create();

        $item_1_1 = $this->itemBuilder->setOrder($order_1)->setProduct($product_1)->create();
        $item_1_2 = $this->itemBuilder->setOrder($order_1)->setProduct($product_1)->create();
        $item_1_3 = $this->itemBuilder->setOrder($order_1)->setProduct($product_1)->create();

        $item_2_1 = $this->itemBuilder->setOrder($order_2)->setProduct($product_1)->create();
        $item_2_2 = $this->itemBuilder->setOrder($order_2)->setProduct($product_2)->create();

        $item_3_1 = $this->itemBuilder->setOrder($order_3)->setProduct($product_2)->create();

        $item_4_1 = $this->itemBuilder->setOrder($order_4)->setProduct($product_1)->create();
        $item_4_2 = $this->itemBuilder->setOrder($order_4)->setProduct($product_2)->create();
        $item_4_3 = $this->itemBuilder->setOrder($order_4)->setProduct($product_1)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'type' => 'success',
                        'message' => env('APP_URL') . "/storage/exports/dealer-order/report-{$now->timestamp}.xlsx"
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function get_report_url_data_empty(): void
    {
        $now = Carbon::now();
        CarbonImmutable::setTestNow($now);

        $this->loginAsDealerWithRole();

        $this->postGraphQL([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'type' => 'success',
                        'message' => env('APP_URL') . "/storage/exports/dealer-order/report-{$now->timestamp}.xlsx"
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

    /** @test */
    public function not_auth(): void
    {
        $this->orderBuilder->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'errors' => [
                    ['message' => "Unauthorized"]
                ]
            ])
        ;
    }

    /** @test */
    public function not_perm(): void
    {
        $dealer = $this->loginAsDealer();

        $this->orderBuilder->setDealer($dealer)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'errors' => [
                    ['message' => "No permission"]
                ]
            ])
        ;
    }
}


