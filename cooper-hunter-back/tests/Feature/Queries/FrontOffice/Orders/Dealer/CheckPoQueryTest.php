<?php

namespace Tests\Feature\Queries\FrontOffice\Orders\Dealer;

use App\GraphQL\Queries\FrontOffice\Orders\Dealer\CheckPoQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\Builders\Orders\Dealer\OrderBuilder;
use Tests\TestCase;

class CheckPoQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = CheckPoQuery::NAME;

    protected DealerBuilder $dealerBuilder;
    protected OrderBuilder $orderBuilder;
    protected CompanyBuilder $companyBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->dealerBuilder = resolve(DealerBuilder::class);
    }

    /** @test */
    public function exist_po(): void
    {
        $company = $this->companyBuilder->create();

        $dealer_1 = $this->dealerBuilder->setCompany($company)->create();
        $dealer_2 = $this->dealerBuilder->setCompany($company)->create();

        $this->loginAsDealerWithRole($dealer_1);

        $po = 'test_po';

        $this->orderBuilder->setDealer($dealer_1)->setData([
            'po' => $po
        ])->create();
        $this->orderBuilder->setDealer($dealer_1)->create();
        $this->orderBuilder->setDealer($dealer_2)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr($po)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => true
                ]
            ])
        ;
    }

    /** @test */
    public function exist_po_another_dealer(): void
    {
        $company = $this->companyBuilder->create();

        $dealer_1 = $this->dealerBuilder->setCompany($company)->create();
        $dealer_2 = $this->dealerBuilder->setCompany($company)->create();

        $this->loginAsDealerWithRole($dealer_2);

        $po = 'test_po';

        $this->orderBuilder->setDealer($dealer_1)->setData([
            'po' => $po
        ])->create();
        $this->orderBuilder->setDealer($dealer_1)->create();
        $this->orderBuilder->setDealer($dealer_2)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr($po)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => true
                ]
            ])
        ;
    }

    /** @test */
    public function not_exist_po(): void
    {
        $company = $this->companyBuilder->create();

        $dealer_1 = $this->dealerBuilder->setCompany($company)->create();
        $dealer_2 = $this->dealerBuilder->setCompany($company)->create();

        $this->loginAsDealerWithRole($dealer_2);

        $po = 'test_po';

        $this->orderBuilder->setDealer($dealer_1)->create();
        $this->orderBuilder->setDealer($dealer_1)->create();
        $this->orderBuilder->setDealer($dealer_2)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr($po)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => false
                ]
            ])
        ;
    }

    /** @test */
    public function exist_po_but_another_company(): void
    {
        $company_1 = $this->companyBuilder->create();
        $company_2 = $this->companyBuilder->create();

        $dealer_1 = $this->dealerBuilder->setCompany($company_1)->create();
        $dealer_2 = $this->dealerBuilder->setCompany($company_2)->create();

        $this->loginAsDealerWithRole($dealer_1);

        $po = 'test_po';

        $this->orderBuilder->setDealer($dealer_1)->create();
        $this->orderBuilder->setDealer($dealer_1)->create();
        $this->orderBuilder->setDealer($dealer_2)->setData([
            'po' => $po
        ])->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr($po)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => false
                ]
            ])
        ;
    }

    protected function getQueryStr($value): string
    {
        return sprintf(
            '
            {
                %s (po: "%s")
            }',
            self::QUERY,
            $value
        );
    }
    /** @test */
    public function not_auth(): void
    {
        $company = $this->companyBuilder->create();

        $dealer_1 = $this->dealerBuilder->setCompany($company)->create();

        $po = 'test_po';

        $this->orderBuilder->setDealer($dealer_1)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr($po)
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
        $company = $this->companyBuilder->create();

        $dealer = $this->dealerBuilder->setCompany($company)->create();

        $this->loginAsDealer($dealer);

        $po = 'test_po';

        $this->orderBuilder->setDealer($dealer)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr($po)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => "No permission"]
                ]
            ])
        ;
    }
}
