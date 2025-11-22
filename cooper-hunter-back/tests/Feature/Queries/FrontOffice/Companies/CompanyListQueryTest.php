<?php

namespace Tests\Feature\Queries\FrontOffice\Companies;

use App\Enums\Companies\CompanyStatus;
use App\GraphQL\Queries\FrontOffice\Companies\CompanyListQuery;
use App\Models\Companies\Corporation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\TestCase;

class CompanyListQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = CompanyListQuery::NAME;

    protected CompanyBuilder $companyBuilder;
    protected DealerBuilder $dealerBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->dealerBuilder = resolve(DealerBuilder::class);
    }

    /** @test */
    public function success_list_for_main_dealer(): void
    {
        $corp = Corporation::factory()->create();
        $corp_1 = Corporation::factory()->create();

        $company_1 = $this->companyBuilder->setCorporation($corp)->setData([
            'business_name' => 'Kalvin'
        ])->create();
        $company_2 = $this->companyBuilder->setCorporation($corp)->setData([
            'business_name' => 'Wood'
        ])->create();
        $company_3 = $this->companyBuilder->setCorporation($corp)->setData([
            'business_name' => 'Alter'
        ])->create();
        $company_4 = $this->companyBuilder->setCorporation($corp_1)->setData([
            'business_name' => 'Alter'
        ])->create();

        $dealer = $this->dealerBuilder->setMain()->setCompany($company_1)->create();
        $this->loginAsDealerWithRole($dealer);

        $this->postGraphQL([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        ['id' => $company_3->id],
                        ['id' => $company_1->id],
                        ['id' => $company_2->id],
                    ],
                ]
            ])
            ->assertJsonCount(3, 'data.'.self::MUTATION)
        ;
    }

    /** @test */
    public function success_list_dealer_corp(): void
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

        $dealer = $this->dealerBuilder->setCompany($company_1)->create();
        $this->loginAsDealerWithRole($dealer);

        $this->postGraphQL([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        ['id' => $company_1->id],
                        ['id' => $company_2->id],
                    ],
                ]
            ])
            ->assertJsonCount(2, 'data.'.self::MUTATION)
        ;
    }

    /** @test */
    public function success_filter_by_status(): void
    {
        $corp = Corporation::factory()->create();

        $company_1 = $this->companyBuilder->setCorporation($corp)->setData([
            'business_name' => 'Kalvin',
            'status' => CompanyStatus::REGISTER
        ])->create();
        $company_2 = $this->companyBuilder->setCorporation($corp)->setData([
            'business_name' => 'Wood',
            'status' => CompanyStatus::REGISTER
        ])->create();
        $company_3 = $this->companyBuilder->setCorporation($corp)->setData([
            'business_name' => 'Alter',
            'status' => CompanyStatus::APPROVE
        ])->create();

        $dealer = $this->dealerBuilder->setCompany($company_1)->create();
        $this->loginAsDealerWithRole($dealer);

        $this->postGraphQL([
            'query' => $this->getQueryStrByStatus(CompanyStatus::REGISTER)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        ['id' => $company_1->id],
                        ['id' => $company_2->id],
                    ],
                ]
            ])
            ->assertJsonCount(2, 'data.'.self::MUTATION)
        ;
    }

    protected function getQueryStr(): string
    {
        return sprintf(
            '
            {
                %s {
                    id
                    business_name
                }
            }',
            self::MUTATION
        );
    }

    protected function getQueryStrByStatus($value): string
    {
        return sprintf(
            '
            {
                %s (status: %s) {
                    id
                    business_name
                }
            }',
            self::MUTATION,
            $value
        );
    }
}

