<?php

namespace Tests\Feature\Mutations\BackOffice\Dealers;

use App\Enums\Companies\CompanyStatus;
use App\Events\Dealers\CreateOrUpdateDealerEvent;
use App\GraphQL\Mutations\BackOffice\Dealers\CreateMutation;
use App\Listeners\Dealers\DealerRegisteredSetRoleListener;
use App\Listeners\Dealers\DealerSendCredentialsListener;
use App\Models\Companies\Company;
use App\Models\Dealers\Dealer;
use App\Models\Technicians\Technician;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Company\CompanyShippingAddressBuilder;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\TestCase;

class CreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = CreateMutation::NAME;

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
    public function success_create(): void
    {
        Event::fake([CreateOrUpdateDealerEvent::class]);

        $this->loginAsSuperAdmin();

        /** @var $company Company */
        $company = $this->companyBuilder->setData([
            'status' => CompanyStatus::REGISTER
        ])->create();
        $company_2 = $this->companyBuilder->setData([
            'status' => CompanyStatus::REGISTER
        ])->create();

        $address_1 = $this->addressBuilder->setCompany($company)->create();
        $address_2 = $this->addressBuilder->setCompany($company)->create();
        $address_3 = $this->addressBuilder->setCompany($company)->create();
        $address_4 = $this->addressBuilder->setCompany($company_2)->create();

        $data = $this->data();
        $data['company_id'] = $company->id;
        $data['shipping_address_ids'] = [
            $address_1->id,
            $address_2->id,
            $address_4->id,
        ];

        $id = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'name' => data_get($data, 'name'),
                        'email' => data_get($data, 'email'),
                        'is_main' => false,
                        'is_main_company' => false,
                        'company' => [
                            'id' => $company->id
                        ],
                        'shipping_addresses' => [
                            ['id' => $address_1->id],
                            ['id' => $address_2->id],
                        ]
                    ],
                ]
            ])
            ->assertJsonCount(2,'data.'.self::MUTATION.'.shipping_addresses')
            ->json('data.'.self::MUTATION.'.id')
        ;
        /** @var Dealer $dealer */
        $dealer = Dealer::find($id);

        $this->assertNotNull($dealer->password);
        $this->assertTrue($dealer->isEmailVerified());
        $this->assertFalse($dealer->isMain());
        $this->assertEquals($dealer->lang, app('localization')->getDefaultSlug());

        Event::assertDispatched(function (CreateOrUpdateDealerEvent $event) use ($dealer) {
            return $event->getDealer()->id === $dealer->id
                && $event->getDealerDto() !== null
                ;
        });
        Event::assertListening(CreateOrUpdateDealerEvent::class, DealerSendCredentialsListener::class);
        Event::assertListening(CreateOrUpdateDealerEvent::class, DealerRegisteredSetRoleListener::class);
    }

    /** @test */
    public function fail_company_not_register(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $company Company */
        $company = $this->companyBuilder->setData([
            'status' => CompanyStatus::APPROVE
        ])->create();

        $data = $this->data();
        $data['company_id'] = $company->id;

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                    [
                        'message' => "validation",
                        "extensions" => [
                            "validation" => [
                                "input.company_id" => [__('validation.company.not_register')]
                            ]
                        ]
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function fail_exist_email_dealer(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $company Company */
        $company = $this->companyBuilder->setData([
            'status' => CompanyStatus::REGISTER
        ])->create();

        $data = $this->data();
        $data['company_id'] = $company->id;

        $this->dealerBuilder->setData([
            'email' => data_get($data, 'email')
        ])->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                    [
                        'message' => "validation",
                        "extensions" => [
                            "validation" => [
                                "input.email" => ["Email is already in use."]
                            ]
                        ]
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function fail_exist_email_member(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $company Company */
        $company = $this->companyBuilder->setData([
            'status' => CompanyStatus::REGISTER
        ])->create();

        $data = $this->data();
        $data['company_id'] = $company->id;

        Technician::factory()->create([
            'email' => data_get($data, 'email')
        ]);

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                    [
                        'message' => "validation",
                        "extensions" => [
                            "validation" => [
                                "input.email" => ["Email is already in use."]
                            ]
                        ]
                    ]
                ]
            ])
        ;
    }

    public function data(): array
    {
        return [
            'email' => $this->faker->safeEmail,
            'name' => $this->faker->name,
            'company_id' => 1,
        ];
    }

    protected function getQueryStr(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    input: {
                        name: "%s"
                        email: "%s"
                        company_id: %s,
                        shipping_address_ids: ["%s", "%s"]
                    }
                ) {
                    id
                    email
                    name
                    is_main
                    is_main_company
                    company {
                        id
                    },
                    shipping_addresses {
                        id
                    }
                }
            }',
            self::MUTATION,
            data_get($data, 'name'),
            data_get($data, 'email'),
            data_get($data, 'company_id'),
            data_get($data, 'shipping_address_ids.0'),
            data_get($data, 'shipping_address_ids.1'),
        );
    }
}
