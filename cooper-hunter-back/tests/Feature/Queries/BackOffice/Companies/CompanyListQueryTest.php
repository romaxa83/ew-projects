<?php

namespace Tests\Feature\Queries\BackOffice\Companies;

use App\Enums\Companies\CompanyStatus;
use App\GraphQL\Queries\BackOffice\Companies\CompanyListQuery;
use App\Models\Companies\Company;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Company\CompanyBuilder;
use Tests\TestCase;

class CompanyListQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = CompanyListQuery::NAME;

    protected CompanyBuilder $companyBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyBuilder = resolve(CompanyBuilder::class);
    }

    /** @test */
    public function success_list(): void
    {
        $this->loginAsSuperAdmin();

        Company::factory()->times(25)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJsonStructure([
                'data' => [
                    self::MUTATION => [
                        ['id', 'business_name']
                    ],
                ]
            ])
            ->assertJsonCount(25, 'data.'.self::MUTATION)
        ;
    }

    /** @test */
    public function success_list_sort(): void
    {
        $this->loginAsSuperAdmin();

        $company_1 = $this->companyBuilder->setData([
            'business_name' => 'Kalvin'
        ])->create();
        $company_2 = $this->companyBuilder->setData([
            'business_name' => 'Wood'
        ])->create();
        $company_3 = $this->companyBuilder->setData([
            'business_name' => 'Alter'
        ])->create();

        $this->postGraphQLBackOffice([
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
    public function success_list_different_corporation(): void
    {
        $this->loginAsSuperAdmin();

        $company_1 = $this->companyBuilder->withCorporation()->create();
        $company_2 = $this->companyBuilder->withCorporation()->create();

        $this->assertNotEquals($company_1->corporation_id, $company_2->corporation_id);

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJsonCount(2, 'data.'.self::MUTATION)
        ;
    }

    /** @test */
    public function success_filter_by_status(): void
    {
        $this->loginAsSuperAdmin();

        Company::factory()->times(5)->create([
            'status' => CompanyStatus::REGISTER
        ]);
        Company::factory()->times(10)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByStatus(CompanyStatus::REGISTER)
        ])
            ->assertJsonStructure([
                'data' => [
                    self::MUTATION => [
                        ['id', 'business_name']
                    ],
                ]
            ])
            ->assertJsonCount(5, 'data.'.self::MUTATION)
        ;
    }

    /** @test */
    public function list_empty(): void
    {
        $this->loginAsSuperAdmin();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJsonCount(0, 'data.'.self::MUTATION)
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

