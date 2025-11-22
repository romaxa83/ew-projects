<?php

namespace Tests\Feature\Queries\BackOffice\Companies;

use App\GraphQL\Queries\BackOffice\Companies\ShippingAddressListQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Company\CompanyShippingAddressBuilder;
use Tests\TestCase;

class ShippingAddressListQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = ShippingAddressListQuery::NAME;

    protected CompanyBuilder $companyBuilder;
    protected CompanyShippingAddressBuilder $addressBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->addressBuilder = resolve(CompanyShippingAddressBuilder::class);
    }

    /** @test */
    public function success_list(): void
    {
        $this->loginAsSuperAdmin();

        $company_1 = $this->companyBuilder->create();
        $company_2 = $this->companyBuilder->create();

        $address_1 = $this->addressBuilder->setData([
            'name' => 'Alfa'
        ])->setCompany($company_1)->create();
        $address_2 = $this->addressBuilder->setData([
            'name' => 'Beta'
        ])->setCompany($company_1)->create();
        $address_3 = $this->addressBuilder->setData([
            'name' => 'Delta'
        ])->setCompany($company_1)->create();
        $address_4 = $this->addressBuilder->setData([
            'name' => 'Gamma'
        ])->setCompany($company_2)->create();
        $address_5 = $this->addressBuilder->setData([
            'name' => 'Epsilon'
        ])->setCompany($company_2)->create();
        $address_6 = $this->addressBuilder->setData([
            'name' => 'w2psilon',
            'active' => false
        ])->setCompany($company_2)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        ['id' => $address_1->id],
                        ['id' => $address_2->id],
                        ['id' => $address_3->id],
                        ['id' => $address_5->id],
                        ['id' => $address_4->id],
                        ['id' => $address_6->id],
                    ],
                ]
            ])
            ->assertJsonCount(6, 'data.'.self::MUTATION)
        ;
    }

    /** @test */
    public function success_list_filter_active(): void
    {
        $this->loginAsSuperAdmin();

        $company_1 = $this->companyBuilder->create();
        $company_2 = $this->companyBuilder->create();

        $address_1 = $this->addressBuilder->setData([
            'name' => 'Alfa'
        ])->setCompany($company_1)->create();
        $address_2 = $this->addressBuilder->setData([
            'name' => 'Beta'
        ])->setCompany($company_1)->create();
        $address_3 = $this->addressBuilder->setData([
            'name' => 'Delta'
        ])->setCompany($company_1)->create();
        $address_4 = $this->addressBuilder->setData([
            'name' => 'Gamma'
        ])->setCompany($company_2)->create();
        $address_5 = $this->addressBuilder->setData([
            'name' => 'Epsilon'
        ])->setCompany($company_2)->create();
        $address_6 = $this->addressBuilder->setData([
            'name' => 'w2psilon',
            'active' => false
        ])->setCompany($company_2)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrActive()
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        ['id' => $address_1->id],
                        ['id' => $address_2->id],
                        ['id' => $address_3->id],
                        ['id' => $address_5->id],
                        ['id' => $address_4->id],
                    ],
                ]
            ])
            ->assertJsonCount(5, 'data.'.self::MUTATION)
        ;
    }

    /** @test */
    public function success_filter_by_status(): void
    {
        $this->loginAsSuperAdmin();

        $company_1 = $this->companyBuilder->create();
        $company_2 = $this->companyBuilder->create();

        $address_1 = $this->addressBuilder->setData([
            'name' => 'Alfa'
        ])->setCompany($company_1)->create();
        $address_2 = $this->addressBuilder->setData([
            'name' => 'Beta'
        ])->setCompany($company_1)->create();
        $address_3 = $this->addressBuilder->setData([
            'name' => 'Delta'
        ])->setCompany($company_1)->create();
        $address_4 = $this->addressBuilder->setData([
            'name' => 'Gamma'
        ])->setCompany($company_2)->create();
        $address_5 = $this->addressBuilder->setData([
            'name' => 'Epsilon'
        ])->setCompany($company_2)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByCompany($company_2->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        ['id' => $address_5->id],
                        ['id' => $address_4->id],
                    ],
                ]
            ])
            ->assertJsonCount(2, 'data.'.self::MUTATION)
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
                    name
                }
            }',
            self::MUTATION
        );
    }

    protected function getQueryStrActive(): string
    {
        return sprintf(
            '
            {
                %s (active: true) {
                    id
                    name
                }
            }',
            self::MUTATION
        );
    }

    protected function getQueryStrByCompany($value): string
    {
        return sprintf(
            '
            {
                %s (company_id: %s) {
                    id
                    name
                }
            }',
            self::MUTATION,
            $value
        );
    }
}


