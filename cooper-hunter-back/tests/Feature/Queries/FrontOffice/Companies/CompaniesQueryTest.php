<?php

namespace Tests\Feature\Queries\FrontOffice\Companies;

use App\GraphQL\Queries\FrontOffice\Companies\CompaniesQuery;
use App\Models\Companies\Corporation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\TestCase;

class CompaniesQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = CompaniesQuery::NAME;

    protected CompanyBuilder $companyBuilder;
    protected DealerBuilder $dealerBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->dealerBuilder = resolve(DealerBuilder::class);
    }

    /** @test */
    public function success_by_id(): void
    {
        $corp = Corporation::factory()->create();
        $corp_2 = Corporation::factory()->create();

        $company_1 = $this->companyBuilder->setCorporation($corp)->setData([
            'business_name' => 'Kalvin'
        ])->create();
        $company_2 = $this->companyBuilder->setCorporation($corp)->setData([
            'business_name' => 'Wood'
        ])->create();
        $company_3 = $this->companyBuilder->setCorporation($corp_2)->setData([
            'business_name' => 'Alter'
        ])->create();

        $dealer = $this->dealerBuilder->setCompany($company_1)->setData([
            'is_main' => true
        ])->create();
        $this->loginAsDealerWithRole($dealer);

        $this->postGraphQL([
            'query' => $this->getQueryStrByID($company_1->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'data' => [
                            ['id' => $company_1->id]
                        ],
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data.'.self::MUTATION.'.data')
        ;
    }

    /** @test */
    public function empty_another_corp(): void
    {
        $corp = Corporation::factory()->create();
        $corp_2 = Corporation::factory()->create();

        $company_1 = $this->companyBuilder->setCorporation($corp)->setData([
            'business_name' => 'Kalvin'
        ])->create();
        $company_2 = $this->companyBuilder->setCorporation($corp)->setData([
            'business_name' => 'Wood'
        ])->create();
        $company_3 = $this->companyBuilder->setCorporation($corp_2)->setData([
            'business_name' => 'Alter'
        ])->create();

        $dealer = $this->dealerBuilder->setCompany($company_1)->setData([
            'is_main' => true
        ])->create();
        $this->loginAsDealerWithRole($dealer);

        $this->postGraphQL([
            'query' => $this->getQueryStrByID($company_3->id)
        ])
            ->assertJsonCount(0, 'data.'.self::MUTATION.'.data')
        ;
    }

//    /** @test */
//    public function empty_not_main_dealer(): void
//    {
//        $corp = Corporation::factory()->create();
//        $corp_2 = Corporation::factory()->create();
//
//        $company_1 = $this->companyBuilder->setCorporation($corp)->setData([
//            'business_name' => 'Kalvin'
//        ])->create();
//        $company_2 = $this->companyBuilder->setCorporation($corp)->setData([
//            'business_name' => 'Wood'
//        ])->create();
//        $company_3 = $this->companyBuilder->setCorporation($corp_2)->setData([
//            'business_name' => 'Alter'
//        ])->create();
//
//        $dealer = $this->dealerBuilder->setCompany($company_1)->create();
//        $this->loginAsDealerWithRole($dealer);
//
//        $this->postGraphQL([
//            'query' => $this->getQueryStrByID($company_1->id)
//        ])
//            ->assertJson([
//                'errors' => [
//                    ['message' => __("exceptions.dealer.not_main")]
//                ]
//            ])
//        ;
//    }

    protected function getQueryStrByID($value): string
    {
        return sprintf(
            '
            {
                %s (id: %s) {
                    data {
                        id
                    }
                }
            }',
            self::MUTATION,
            $value
        );
    }

    /** @test */
    public function not_auth(): void
    {
        $corp = Corporation::factory()->create();
        $company_1 = $this->companyBuilder->setCorporation($corp)->create();
        $dealer = $this->dealerBuilder->setCompany($company_1)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStrByID($company_1->id)
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
        $corp = Corporation::factory()->create();
        $company_1 = $this->companyBuilder->setCorporation($corp)->create();
        $dealer = $this->dealerBuilder->setCompany($company_1)->create();

        $this->loginAsDealer($dealer);

        $this->postGraphQL([
            'query' => $this->getQueryStrByID($company_1->id)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => "No permission"]
                ]
            ])
        ;
    }
}

