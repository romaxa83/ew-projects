<?php

namespace Tests\Feature\Queries\FrontOffice\Companies;

use App\GraphQL\Queries\FrontOffice\Companies\ShippingAddressListQuery;
use App\Models\Companies\Corporation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Company\CompanyShippingAddressBuilder;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\TestCase;

class ShippingAddressListQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = ShippingAddressListQuery::NAME;

    protected CompanyBuilder $companyBuilder;
    protected CompanyShippingAddressBuilder $addressBuilder;
    protected DealerBuilder $dealerBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->addressBuilder = resolve(CompanyShippingAddressBuilder::class);
        $this->dealerBuilder = resolve(DealerBuilder::class);
    }

    /** @test */
    public function success_list_for_main_dealer(): void
    {
        $corp_1 = Corporation::factory()->create();
        $corp_2 = Corporation::factory()->create();

        $company_1 = $this->companyBuilder->setCorporation($corp_1)->create();
        $company_2 = $this->companyBuilder->setCorporation($corp_1)->create();
        $company_3 = $this->companyBuilder->setCorporation($corp_2)->create();
        // check
        $address_1 = $this->addressBuilder->setCompany($company_1)->setName('aaa')->create();
        $address_2 = $this->addressBuilder->setCompany($company_1)->setName('baa')->create();
        $address_3 = $this->addressBuilder->setCompany($company_1)->setName('daa')->create();
        $address_4 = $this->addressBuilder->setCompany($company_2)->setName('caa')->create();
        // not check
        $address_7 = $this->addressBuilder->setCompany($company_2)
            ->setData(['active' => false])->setName('zaa')->create();

        $address_5 = $this->addressBuilder->setCompany($company_3)->create();
        $address_6 = $this->addressBuilder->setCompany($company_3)->create();

        $dealer = $this->dealerBuilder->setMain()->setNotMainCompany()->setCompany($company_1)->create();
        $this->loginAsDealerWithRole($dealer);

        $this->postGraphQL([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        ['id' => $address_1->id],
                        ['id' => $address_2->id],
                        ['id' => $address_4->id],
                        ['id' => $address_3->id],
                        ['id' => $address_7->id],
                    ],
                ]
            ])
            ->assertJsonCount(5, 'data.'.self::MUTATION)
        ;
    }

    /** @test */
    public function success_list_for_main_dealer_filter_active(): void
    {
        $corp_1 = Corporation::factory()->create();
        $corp_2 = Corporation::factory()->create();

        $company_1 = $this->companyBuilder->setCorporation($corp_1)->create();
        $company_2 = $this->companyBuilder->setCorporation($corp_1)->create();
        $company_3 = $this->companyBuilder->setCorporation($corp_2)->create();
        // check
        $address_1 = $this->addressBuilder->setCompany($company_1)->setName('aaa')->create();
        $address_2 = $this->addressBuilder->setCompany($company_1)->setName('baa')->create();
        $address_3 = $this->addressBuilder->setCompany($company_1)->setName('daa')->create();
        $address_4 = $this->addressBuilder->setCompany($company_2)->setName('caa')->create();
        // not check
        $address_7 = $this->addressBuilder->setCompany($company_2)
            ->setData(['active' => false])->setName('zaa')->create();

        $address_5 = $this->addressBuilder->setCompany($company_3)->create();
        $address_6 = $this->addressBuilder->setCompany($company_3)->create();

        $dealer = $this->dealerBuilder->setMain()->setNotMainCompany()->setCompany($company_1)->create();
        $this->loginAsDealerWithRole($dealer);

        $this->postGraphQL([
            'query' => $this->getQueryStrActive()
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        ['id' => $address_1->id],
                        ['id' => $address_2->id],
                        ['id' => $address_4->id],
                        ['id' => $address_3->id],
                    ],
                ]
            ])
            ->assertJsonCount(4, 'data.'.self::MUTATION)
        ;
    }

    /** @test */
    public function success_list_for_main_dealer_and_main_company(): void
    {
        $corp_1 = Corporation::factory()->create();
        $corp_2 = Corporation::factory()->create();

        $company_1 = $this->companyBuilder->setCorporation($corp_1)->create();
        $company_2 = $this->companyBuilder->setCorporation($corp_1)->create();
        $company_3 = $this->companyBuilder->setCorporation($corp_2)->create();
        // check
        $address_1 = $this->addressBuilder->setCompany($company_1)->setName('aaa')->create();
        $address_2 = $this->addressBuilder->setCompany($company_1)->setName('baa')->create();
        $address_3 = $this->addressBuilder->setCompany($company_1)->setName('daa')->create();
        $address_4 = $this->addressBuilder->setCompany($company_2)->setName('caa')->create();
        // not check
        $address_5 = $this->addressBuilder->setCompany($company_3)->create();
        $address_6 = $this->addressBuilder->setCompany($company_3)->create();

        $dealer = $this->dealerBuilder->setMain()->setCompany($company_1)->create();
        $this->loginAsDealerWithRole($dealer);

        $this->postGraphQL([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        ['id' => $address_1->id],
                        ['id' => $address_2->id],
                        ['id' => $address_4->id],
                        ['id' => $address_3->id],
                    ],
                ]
            ])
            ->assertJsonCount(4, 'data.'.self::MUTATION)
        ;
    }

    /** @test */
    public function success_list_for_main_dealer_filter_company_id(): void
    {
        $corp_1 = Corporation::factory()->create();
        $corp_2 = Corporation::factory()->create();

        $company_1 = $this->companyBuilder->setCorporation($corp_1)->create();
        $company_2 = $this->companyBuilder->setCorporation($corp_1)->create();
        $company_3 = $this->companyBuilder->setCorporation($corp_2)->create();
        // check
        $address_1 = $this->addressBuilder->setCompany($company_1)->setName('aaa')->create();
        $address_2 = $this->addressBuilder->setCompany($company_1)->setName('baa')->create();
        $address_3 = $this->addressBuilder->setCompany($company_1)->setName('daa')->create();
        // not check
        $address_4 = $this->addressBuilder->setCompany($company_2)->setName('caa')->create();
        $address_5 = $this->addressBuilder->setCompany($company_3)->create();
        $address_6 = $this->addressBuilder->setCompany($company_3)->create();

        $dealer = $this->dealerBuilder->setNotMainCompany()->setMain()->setCompany($company_1)->create();
        $this->loginAsDealerWithRole($dealer);

        $this->postGraphQL([
            'query' => $this->getQueryStrCompanyId($company_1->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        ['id' => $address_1->id],
                        ['id' => $address_2->id],
                        ['id' => $address_3->id],
                    ],
                ]
            ])
            ->assertJsonCount(3, 'data.'.self::MUTATION)
        ;
    }

    /** @test */
    public function success_list_for_main_company_dealer(): void
    {
        $corp_1 = Corporation::factory()->create();
        $corp_2 = Corporation::factory()->create();

        $company_1 = $this->companyBuilder->setCorporation($corp_1)->create();
        $company_2 = $this->companyBuilder->setCorporation($corp_1)->create();
        $company_3 = $this->companyBuilder->setCorporation($corp_2)->create();
        // check
        $address_1 = $this->addressBuilder->setCompany($company_1)->setName('aaa')->create();
        $address_2 = $this->addressBuilder->setCompany($company_1)->setName('caa')->create();
        $address_3 = $this->addressBuilder->setCompany($company_1)->setName('baa')->create();
        // not check
        $address_4 = $this->addressBuilder->setCompany($company_2)->setName('caa')->create();
        $address_5 = $this->addressBuilder->setCompany($company_3)->create();
        $address_6 = $this->addressBuilder->setCompany($company_3)->create();

        $dealer = $this->dealerBuilder->setCompany($company_1)->create();
        $this->loginAsDealerWithRole($dealer);

        $this->postGraphQL([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        ['id' => $address_1->id],
                        ['id' => $address_3->id],
                        ['id' => $address_2->id],
                    ],
                ]
            ])
            ->assertJsonCount(3, 'data.'.self::MUTATION)
        ;
    }

    /** @test */
    public function success_list_for_simple_dealer(): void
    {
        $corp_1 = Corporation::factory()->create();
        $corp_2 = Corporation::factory()->create();

        $company_1 = $this->companyBuilder->setCorporation($corp_1)->create();
        $company_2 = $this->companyBuilder->setCorporation($corp_1)->create();
        $company_3 = $this->companyBuilder->setCorporation($corp_2)->create();
        // check
        $address_1 = $this->addressBuilder->setCompany($company_1)->setName('aaa')->create();
        $address_2 = $this->addressBuilder->setCompany($company_1)->setName('caa')->create();
        $address_3 = $this->addressBuilder->setCompany($company_1)->setName('baa')->create();
        // not check
        $address_4 = $this->addressBuilder->setCompany($company_2)->setName('caa')->create();
        $address_5 = $this->addressBuilder->setCompany($company_3)->create();
        $address_6 = $this->addressBuilder->setCompany($company_3)->create();

        $dealer = $this->dealerBuilder->setNotMainCompany()
            ->setCompany($company_1)->setAddresses($address_1, $address_3)->create();
        $this->loginAsDealerWithRole($dealer);

        $this->postGraphQL([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        ['id' => $address_1->id],
                        ['id' => $address_3->id]
                    ],
                ]
            ])
            ->assertJsonCount(2, 'data.'.self::MUTATION)
        ;
    }

    /** @test */
    public function list_for_simple_dealer_with_company_id(): void
    {
        $corp_1 = Corporation::factory()->create();
        $corp_2 = Corporation::factory()->create();

        $company_1 = $this->companyBuilder->setCorporation($corp_1)->create();
        $company_2 = $this->companyBuilder->setCorporation($corp_1)->create();
        $company_3 = $this->companyBuilder->setCorporation($corp_2)->create();
        // check
        $address_1 = $this->addressBuilder->setCompany($company_1)->setName('aaa')->create();
        $address_2 = $this->addressBuilder->setCompany($company_1)->setName('caa')->create();
        $address_3 = $this->addressBuilder->setCompany($company_1)->setName('baa')->create();
        // not check
        $address_4 = $this->addressBuilder->setCompany($company_2)->setName('caa')->create();
        $address_5 = $this->addressBuilder->setCompany($company_3)->create();
        $address_6 = $this->addressBuilder->setCompany($company_3)->create();

        $dealer = $this->dealerBuilder->setNotMainCompany()
            ->setCompany($company_1)->setAddresses($address_1, $address_3)->create();
        $this->loginAsDealerWithRole($dealer);

        $this->postGraphQL([
            'query' => $this->getQueryStrCompanyId($company_2->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        ['id' => $address_1->id],
                        ['id' => $address_3->id]
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
                %s (active:true) {
                    id
                    name
                }
            }',
            self::MUTATION
        );
    }

    protected function getQueryStrCompanyId($id): string
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
            $id
        );
    }
}

