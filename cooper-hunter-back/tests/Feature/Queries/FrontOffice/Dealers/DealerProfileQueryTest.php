<?php

namespace Tests\Feature\Queries\FrontOffice\Dealers;

use App\GraphQL\Queries\FrontOffice\Dealers\DealerProfileQuery;
use App\Models\Companies\Company;
use App\Models\Companies\Corporation;
use App\Models\Dealers\Dealer;
use App\Models\Permissions\Permission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Company\CompanyShippingAddressBuilder;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class DealerProfileQueryTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const QUERY = DealerProfileQuery::NAME;

    protected CompanyBuilder $companyBuilder;
    protected CompanyShippingAddressBuilder $addressBuilder;
    protected DealerBuilder $dealerBuilder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->dealerBuilder = resolve(DealerBuilder::class);
        $this->addressBuilder = resolve(CompanyShippingAddressBuilder::class);
    }

    /** @test */
    public function get_profile(): void
    {
        /** @var Collection $permissions */
        $permissions = Permission::factory()->dealer()
            ->count(5)->create();
        $role = $this->generateRole(
            'dealer-role',
            $permissions->pluck('name')->all(),
            Dealer::GUARD,
        );
        /** @var Company $company */
        $company = $this->companyBuilder->withManager()->create();
        /** @var Dealer $dealer */
        $dealer = $this->dealerBuilder->setCompany($company)->create();

        $dealer->assignRole($role);

        $this->loginAsDealer($dealer);

        $this->query()
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'id' => $dealer->id,
                            'email' => $dealer->email->getValue(),
                            'is_main' => $dealer->is_main,
                            'is_main_company' => $dealer->is_main_company,
                            'name' => $dealer->first_name,
                            'is_verify_email' => $dealer->isEmailVerified(),
                            'company' => [
                                'id' => $company->id,
                                'manager' => [
                                    'name' => $company->manager->name,
                                    'email' => $company->manager->email->getValue(),
                                    'phone' => $company->manager->phone->getValue(),
                                ],
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(5, 'data.'.self::QUERY.'.permissions')
        ;
    }

    /** @test */
    public function get_addresses_for_simple_dealer(): void
    {
        /** @var Company $company */
        $company = $this->companyBuilder->create();

        $address_1 = $this->addressBuilder->setCompany($company)->create();
        $address_2 = $this->addressBuilder->setCompany($company)->create();
        $address_3 = $this->addressBuilder->setCompany($company)->create();
        /** @var Dealer $dealer */
        $dealer = $this->dealerBuilder->setData([
            'is_main' => false,
            'is_main_company' => false,
        ])
            ->setAddresses($address_1, $address_2)
            ->setCompany($company)->create();

        $this->loginAsDealer($dealer);

        $this->query()
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'id' => $dealer->id,
                            'shipping_addresses' => [
                                [
                                    'id' => $address_1->id,
                                    'name' => $address_1->name
                                ],
                                [
                                    'id' => $address_2->id,
                                    'name' => $address_2->name,
                                ]
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.'.self::QUERY.'.shipping_addresses')
        ;
    }

    /** @test */
    public function get_addresses_for_main_company_dealer(): void
    {
        /** @var Company $company */
        $company = $this->companyBuilder->create();

        $address_1 = $this->addressBuilder->setCompany($company)->create();
        $address_2 = $this->addressBuilder->setCompany($company)->create();
        $address_3 = $this->addressBuilder->setCompany($company)->create();
        /** @var Dealer $dealer */
        $dealer = $this->dealerBuilder->setData([
            'is_main' => false,
            'is_main_company' => true,
        ])
            ->setAddresses($address_1, $address_2)
            ->setCompany($company)->create();

        $this->loginAsDealer($dealer);

        $this->query()
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'id' => $dealer->id,
                            'shipping_addresses' => [
                                [
                                    'id' => $address_1->id,
                                    'name' => $address_1->name
                                ],
                                [
                                    'id' => $address_2->id,
                                    'name' => $address_2->name,
                                ],
                                [
                                    'id' => $address_3->id,
                                    'name' => $address_3->name,
                                ]
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(3, 'data.'.self::QUERY.'.shipping_addresses')
        ;
    }

    /** @test */
    public function get_addresses_for_main_dealer(): void
    {
        $corp = Corporation::factory()->create();
        /** @var Company $company */
        $company = $this->companyBuilder->setCorporation($corp)->create();
        $company_1 = $this->companyBuilder->setCorporation($corp)->create();

        $address_1 = $this->addressBuilder->setCompany($company)->create();
        $address_2 = $this->addressBuilder->setCompany($company)->create();
        $address_3 = $this->addressBuilder->setCompany($company)->create();
        $address_4 = $this->addressBuilder->setCompany($company_1)->create();
        /** @var Dealer $dealer */
        $dealer = $this->dealerBuilder->setData([
            'is_main' => true,
            'is_main_company' => true,
        ])
            ->setAddresses($address_1, $address_2)
            ->setCompany($company)->create();

        $this->loginAsDealer($dealer);

        $this->query()
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'id' => $dealer->id,
                            'shipping_addresses' => [
                                [
                                    'id' => $address_1->id,
                                    'name' => $address_1->name
                                ],
                                [
                                    'id' => $address_2->id,
                                    'name' => $address_2->name,
                                ],
                                [
                                    'id' => $address_3->id,
                                    'name' => $address_3->name,
                                ],
                                [
                                    'id' => $address_4->id,
                                    'name' => $address_4->name,
                                ]
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(4, 'data.'.self::QUERY.'.shipping_addresses')
        ;
    }

    protected function query(): TestResponse
    {
        $query = sprintf(
            'query {
                %s {
                    id
                    email
                    name
                    is_main
                    is_main_company
                    is_verify_email
                    language {
                        name
                        slug
                    }
                    company {
                        id
                        terms
                        manager {
                            name
                            email
                            phone
                        }
                    }
                    shipping_addresses {
                        id
                        name
                    }
                    permissions {
                        id
                        name
                    }
                }
            }',
            self::QUERY
        );

        return $this->postGraphQL(compact('query'));
    }
}
