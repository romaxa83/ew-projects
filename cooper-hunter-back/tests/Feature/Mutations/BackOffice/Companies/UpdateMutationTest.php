<?php

namespace Tests\Feature\Mutations\BackOffice\Companies;

use App\Enums\Companies\CompanyType;
use App\Events\Companies\CreateOrUpdateCompanyEvent;
use App\GraphQL\Mutations\BackOffice\Companies;
use App\Listeners\Companies\SendDataToOnecListeners;
use App\Models\Companies\Company;
use App\Models\Companies\Corporation;
use App\Models\Locations\State;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Company\CompanyShippingAddressBuilder;
use Tests\TestCase;

class UpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = Companies\UpdateMutation::NAME;

    protected CompanyBuilder $companyBuilder;
    protected CompanyShippingAddressBuilder $companyShippingAddressBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->companyShippingAddressBuilder = resolve(CompanyShippingAddressBuilder::class);
    }

    /** @test */
    public function update(): void
    {
        Event::fake([CreateOrUpdateCompanyEvent::class]);

        $this->loginAsSuperAdmin();

        $corp = Corporation::factory()->create();
        /** @var $company Company */
        $company = $this->companyBuilder->withContacts()
            ->setCorporation($corp)->create();

        $address_1 = $this->companyShippingAddressBuilder->setCompany($company)->create();
        $address_2 = $this->companyShippingAddressBuilder->setCompany($company)->create();

        $data = $this->data();

        $this->assertNotEquals($company->type, data_get($data, 'dealer_info.type'));
        $this->assertNotEquals($company->business_name, data_get($data, 'dealer_info.business_name'));
        $this->assertNotEquals($company->email, data_get($data, 'dealer_info.contact_email'));
        $this->assertNotEquals($company->phone, data_get($data, 'dealer_info.phone'));
        $this->assertNotEquals($company->fax, data_get($data, 'dealer_info.fax'));
        $this->assertNotEquals($company->state_id, data_get($data, 'dealer_info.state_id'));
        $this->assertNotEquals($company->city, data_get($data, 'dealer_info.city'));
        $this->assertNotEquals($company->address_line_1, data_get($data, 'dealer_info.address_line_1'));
        $this->assertNotEquals($company->address_line_2, data_get($data, 'dealer_info.address_line_2'));
        $this->assertNotEquals($company->zip, data_get($data, 'dealer_info.zip'));
        $this->assertNotEquals($company->po_box, data_get($data, 'dealer_info.po_box'));
        $this->assertNotEquals($company->taxpayer_id, data_get($data, 'dealer_info.taxpayer_id'));
        $this->assertNotEquals($company->tax, data_get($data, 'dealer_info.tax'));
        $this->assertNotEquals($company->websites, data_get($data, 'dealer_info.websites'));
        $this->assertNotEquals($company->marketplaces, data_get($data, 'dealer_info.marketplaces'));
        $this->assertNotEquals($company->trade_names, data_get($data, 'dealer_info.trade_names'));

        $this->assertNotEquals($company->contactAccount->name, data_get($data, 'contact_account.name'));
        $this->assertNotEquals($company->contactAccount->email, data_get($data, 'contact_account.email'));
        $this->assertNotEquals($company->contactAccount->phone, data_get($data, 'contact_account.phone'));
        $this->assertNotEquals($company->contactAccount->state_id, data_get($data, 'contact_account.state_id'));
        $this->assertNotEquals($company->contactAccount->city, data_get($data, 'contact_account.city'));
        $this->assertNotEquals($company->contactAccount->address_line_1, data_get($data, 'contact_account.address_line_1'));
        $this->assertNotEquals($company->contactAccount->address_line_2, data_get($data, 'contact_account.address_line_2'));
        $this->assertNotEquals($company->contactAccount->zip, data_get($data, 'contact_account.zip'));
        $this->assertNotEquals($company->contactAccount->po_box, data_get($data, 'contact_account.po_box'));

        $this->assertNotEquals($company->contactOrder->name, data_get($data, 'contact_order.name'));
        $this->assertNotEquals($company->contactOrder->email, data_get($data, 'contact_order.email'));
        $this->assertNotEquals($company->contactOrder->phone, data_get($data, 'contact_order.phone'));
        $this->assertNotEquals($company->contactOrder->state_id, data_get($data, 'contact_order.state_id'));
        $this->assertNotEquals($company->contactOrder->city, data_get($data, 'contact_order.city'));
        $this->assertNotEquals($company->contactOrder->address_line_1, data_get($data, 'contact_order.address_line_1'));
        $this->assertNotEquals($company->contactOrder->address_line_2, data_get($data, 'contact_order.address_line_2'));
        $this->assertNotEquals($company->contactOrder->zip, data_get($data, 'contact_order.zip'));
        $this->assertNotEquals($company->contactOrder->po_box, data_get($data, 'contact_order.po_box'));

        $this->assertCount(2, $company->shippingAddresses);
        $this->assertNotEquals($company->shippingAddresses[0]->name, data_get($data, 'shipping_address.0.name'));
        $this->assertEquals($company->shippingAddresses[0]->active, (bool)data_get($data, 'shipping_address.0.active'));
        $this->assertNotEquals($company->shippingAddresses[0]->fax, data_get($data, 'shipping_address.0.fax'));
        $this->assertNotEquals($company->shippingAddresses[0]->email, data_get($data, 'shipping_address.0.email'));
        $this->assertNotEquals($company->shippingAddresses[0]->receiving_persona, data_get($data, 'shipping_address.0.receiving_persona'));
        $this->assertNotEquals($company->shippingAddresses[0]->phone, data_get($data, 'shipping_address.0.phone'));
        $this->assertNotEquals($company->shippingAddresses[0]->state_id, data_get($data, 'shipping_address.0.state_id'));
        $this->assertNotEquals($company->shippingAddresses[0]->city, data_get($data, 'shipping_address.0.city'));
        $this->assertNotEquals($company->shippingAddresses[0]->address_line_1, data_get($data, 'shipping_address.0.address_line_1'));
        $this->assertNotEquals($company->shippingAddresses[0]->address_line_2, data_get($data, 'shipping_address.0.address_line_2'));
        $this->assertNotEquals($company->shippingAddresses[0]->zip, data_get($data, 'shipping_address.0.zip'));
        $this->assertNotEquals($company->shippingAddresses[1]->name, data_get($data, 'shipping_address.1.name'));
        $this->assertNotEquals($company->shippingAddresses[1]->active, data_get($data, 'shipping_address.1.active'));
        $this->assertNotEquals($company->shippingAddresses[1]->fax, data_get($data, 'shipping_address.1.fax'));
        $this->assertNotEquals($company->shippingAddresses[1]->email, data_get($data, 'shipping_address.1.email'));
        $this->assertNotEquals($company->shippingAddresses[1]->receiving_persona, data_get($data, 'shipping_address.1.receiving_persona'));
        $this->assertNotEquals($company->shippingAddresses[1]->phone, data_get($data, 'shipping_address.1.phone'));
        $this->assertNotEquals($company->shippingAddresses[1]->state_id, data_get($data, 'shipping_address.1.state_id'));
        $this->assertNotEquals($company->shippingAddresses[1]->city, data_get($data, 'shipping_address.1.city'));
        $this->assertNotEquals($company->shippingAddresses[1]->address_line_1, data_get($data, 'shipping_address.1.address_line_1'));
        $this->assertNotEquals($company->shippingAddresses[1]->address_line_2, data_get($data, 'shipping_address.1.address_line_2'));
        $this->assertNotEquals($company->shippingAddresses[1]->zip, data_get($data, 'shipping_address.1.zip'));

        $data['shipping_address'][0]['id'] = $address_1->id;
        $data['shipping_address'][1]['id'] = $address_2->id;
        $data['id'] = $company->id;

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $company->id,
                        'status' => $company->status,
                        'type' => data_get($data, 'company_info.type'),
                        'business_name' => data_get($data, 'company_info.business_name'),
                        'email' => data_get($data, 'company_info.email'),
                        'phone' => data_get($data, 'company_info.phone'),
                        'fax' => data_get($data, 'company_info.fax'),
                        'state' => [
                            'id' => data_get($data, 'company_info.state_id')
                        ],
                        'corporation' => [
                            'id' => $corp->id
                        ],
                        'city' => data_get($data, 'company_info.city'),
                        'address_line_1' => data_get($data, 'company_info.address_line_1'),
                        'address_line_2' => data_get($data, 'company_info.address_line_2'),
                        'zip' => data_get($data, 'company_info.zip'),
                        'po_box' => data_get($data, 'company_info.po_box'),
                        'taxpayer_id' => data_get($data, 'company_info.taxpayer_id'),
                        'tax' => data_get($data, 'company_info.tax'),
                        'websites' => data_get($data, 'company_info.websites'),
                        'marketplaces' => data_get($data, 'company_info.marketplaces'),
                        'trade_names' => [],
                        'shipping_addresses' => [
                            [
                                'name' => data_get($data, 'shipping_address.0.name'),
                                'active' => true,
                                'phone' => data_get($data, 'shipping_address.0.phone'),
                                'fax' => data_get($data, 'shipping_address.0.fax'),
                                'email' => data_get($data, 'shipping_address.0.email'),
                                'receiving_persona' => data_get($data, 'shipping_address.0.receiving_persona'),
                                'country' => [
                                    'country_code' => data_get($data, 'shipping_address.0.country_code')
                                ],
                                'state' => [
                                    'id' => data_get($data, 'shipping_address.0.state_id')
                                ],
                                'city' => data_get($data, 'shipping_address.0.city'),
                                'address_line_1' => data_get($data, 'shipping_address.0.address_line_1'),
                                'address_line_2' => data_get($data, 'shipping_address.0.address_line_2'),
                                'zip' => data_get($data, 'shipping_address.0.zip'),
                            ],
                            [
                                'name' => data_get($data, 'shipping_address.1.name'),
                                'active' => false,
                                'phone' => data_get($data, 'shipping_address.1.phone'),
                                'fax' => data_get($data, 'shipping_address.1.fax'),
                                'email' => data_get($data, 'shipping_address.1.email'),
                                'receiving_persona' => data_get($data, 'shipping_address.1.receiving_persona'),
                                'country' => [
                                    'country_code' => data_get($data, 'shipping_address.1.country_code')
                                ],
                                'state' => [
                                    'id' => data_get($data, 'shipping_address.1.state_id')
                                ],
                                'city' => data_get($data, 'shipping_address.1.city'),
                                'address_line_1' => data_get($data, 'shipping_address.1.address_line_1'),
                                'address_line_2' => data_get($data, 'shipping_address.1.address_line_2'),
                                'zip' => data_get($data, 'shipping_address.1.zip'),
                            ],
                            [
                                'name' => data_get($data, 'shipping_address.2.name'),
                                'active' => true,
                                'phone' => data_get($data, 'shipping_address.2.phone'),
                                'fax' => data_get($data, 'shipping_address.2.fax'),
                                'email' => data_get($data, 'shipping_address.2.email'),
                                'receiving_persona' => data_get($data, 'shipping_address.2.receiving_persona'),
                                'country' => [
                                    'country_code' => data_get($data, 'shipping_address.2.country_code')
                                ],
                                'state' => [
                                    'id' => data_get($data, 'shipping_address.2.state_id')
                                ],
                                'city' => data_get($data, 'shipping_address.2.city'),
                                'address_line_1' => data_get($data, 'shipping_address.2.address_line_1'),
                                'address_line_2' => data_get($data, 'shipping_address.2.address_line_2'),
                                'zip' => data_get($data, 'shipping_address.2.zip'),
                            ]
                        ],
                        'contact_account' => [
                            'name' => data_get($data, 'contact_account.name'),
                            'email' => data_get($data, 'contact_account.email'),
                            'phone' => data_get($data, 'contact_account.phone'),
                            'country' => [
                                'country_code' => data_get($data, 'contact_account.country_code')
                            ],
                            'state' => [
                                'id' => data_get($data, 'contact_account.state_id')
                            ],
                            'city' => data_get($data, 'contact_account.city'),
                            'address_line_1' => data_get($data, 'contact_account.address_line_1'),
                            'address_line_2' => data_get($data, 'contact_account.address_line_2'),
                            'zip' => data_get($data, 'contact_account.zip'),
                            'po_box' => data_get($data, 'contact_account.po_box'),
                        ],
                        'contact_order' => [
                            'name' => data_get($data, 'contact_order.name'),
                            'email' => data_get($data, 'contact_order.email'),
                            'phone' => data_get($data, 'contact_order.phone'),
                            'country' => [
                                'country_code' => data_get($data, 'contact_order.country_code')
                            ],
                            'state' => [
                                'id' => data_get($data, 'contact_order.state_id')
                            ],
                            'city' => data_get($data, 'contact_order.city'),
                            'address_line_1' => data_get($data, 'contact_order.address_line_1'),
                            'address_line_2' => data_get($data, 'contact_order.address_line_2'),
                            'zip' => data_get($data, 'contact_order.zip'),
                            'po_box' => data_get($data, 'contact_order.po_box'),
                        ]
                    ],
                ]
            ])
        ;

        Event::assertDispatched(function (CreateOrUpdateCompanyEvent $event) use ($company) {
            return $event->getCompany()->id === $company->id;
        });
        Event::assertListening(CreateOrUpdateCompanyEvent::class, SendDataToOnecListeners::class);
    }

    /** @test */
    public function update_without_location_email(): void
    {
        Event::fake([CreateOrUpdateCompanyEvent::class]);

        $this->loginAsSuperAdmin();

        $corp = Corporation::factory()->create();
        /** @var $company Company */
        $company = $this->companyBuilder->withContacts()
            ->setCorporation($corp)->create();

        $address_1 = $this->companyShippingAddressBuilder->setCompany($company)->create();
        $address_2 = $this->companyShippingAddressBuilder->setCompany($company)->create();

        $data = $this->data();

        $data['shipping_address'][0]['id'] = $address_1->id;
        $data['shipping_address'][0]['email'] = null;
        $data['shipping_address'][0]['receiving_persona'] = null;
        $data['shipping_address'][1]['id'] = $address_2->id;
        $data['shipping_address'][1]['email'] = null;
        $data['shipping_address'][1]['receiving_persona'] = null;
        $data['id'] = $company->id;

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                    ['message' => 'validation'],
                ]
            ])
        ;
    }

    /** @test */
    public function update_with_corp(): void
    {
        Event::fake([CreateOrUpdateCompanyEvent::class]);

        $this->loginAsSuperAdmin();

        $corp_1 = Corporation::factory()->create();
        $corp_2 = Corporation::factory()->create();
        /** @var $company Company */
        $company = $this->companyBuilder->withContacts()
            ->setCorporation($corp_1)->create();

        $data = $this->data();

        $data['id'] = $company->id;
        $data['company_info']['corporation_id'] = $corp_2->id;

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrWithCorp($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $company->id,
                        'corporation' => [
                            'id' => $corp_2->id
                        ],
                    ],
                ]
            ])
        ;

        Event::assertListening(CreateOrUpdateCompanyEvent::class, SendDataToOnecListeners::class);
    }

    /** @test */
    public function update_validation_for_uniq()
    {
        $this->loginAsSuperAdmin();

        /** @var $company Company */
        $company = $this->companyBuilder->withContacts()->create();

        $address_1 = $this->companyShippingAddressBuilder->setCompany($company)->create();
        $address_2 = $this->companyShippingAddressBuilder->setCompany($company)->create();

        $data = $this->data();
        $data['company_info']['email'] = $company->email->getValue();
        $data['company_info']['phone'] = $company->phone->getValue();
        $data['company_info']['fax'] = $company->fax;
        $data['company_info']['taxpayer_id'] = $company->taxpayer_id;

        $data['shipping_address'][0]['id'] = $address_1->id;
        $data['shipping_address'][0]['phone'] = $address_1->phone->getValue();
        $data['shipping_address'][0]['fax'] = $address_1->fax->getValue();
        $data['shipping_address'][0]['email'] = $address_1->email->getValue();
        $data['shipping_address'][1]['id'] = $address_2->id;
        $data['shipping_address'][1]['phone'] = $address_2->phone->getValue();
        $data['shipping_address'][1]['fax'] = $address_2->fax->getValue();
        $data['shipping_address'][1]['email'] = $address_2->email->getValue();
        $data['id'] = $company->id;

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $company->id,
                        'email' => $company->email->getValue(),
                        'phone' => $company->phone->getValue(),
                        'fax' => $company->fax->getValue(),
                        'taxpayer_id' => $company->taxpayer_id,
                    ],
                ]
            ])
        ;
    }

    public function data(): array
    {
        list(
            $state_1,
            $state_2,
            $state_3,
            $state_4,
            ) = State::with('country')->limit(4)->latest('id')->get();

        return [
            'company_info' => [
                'type' => CompanyType::OTHER(),
                'business_name' => $this->faker->company,
                'email' => $this->faker->safeEmail,
                'phone' => '39099990901',
                'fax' => '39099990902',
                'country_code' => $state_1->country->country_code,
                'state_id' => $state_1->id,
                'city' => 'some city',
                'address_line_1' => $this->faker->streetName,
                'address_line_2' => $this->faker->streetName,
                'zip' => $this->faker->postcode,
                'po_box' => $this->faker->postcode,
                'taxpayer_id' => $this->faker->creditCardNumber,
                'tax' => $this->faker->numerify,
                'websites' => [
                    $this->faker->url,
                    $this->faker->url,
                    $this->faker->url,
                ],
                'marketplaces' => [
                    $this->faker->url,
                    $this->faker->url,
                ],
                'trade_names' => [
                    $this->faker->company,
                    $this->faker->company,
                ],
            ],
            'shipping_address' => [
                [
                    'active' => 'true',
                    'name' => $this->faker->company,
                    'phone' => '38099990903',
                    'fax' => '38099990904',
                    'email' => $this->faker->unique()->safeEmail,
                    'receiving_persona' => $this->faker->userName,
                    'country_code' => $state_1->country->country_code,
                    'state_id' => $state_1->id,
                    'city' => $this->faker->city,
                    'address_line_1' => $this->faker->streetName,
                    'address_line_2' => $this->faker->streetName,
                    'zip' => $this->faker->postcode,
                ],
                [
                    'active' => 'false',
                    'name' => $this->faker->company,
                    'phone' => '38099990905',
                    'fax' => '38099990906',
                    'email' => $this->faker->unique()->safeEmail,
                    'receiving_persona' => $this->faker->userName,
                    'country_code' => $state_4->country->country_code,
                    'state_id' => $state_4->id,
                    'city' => $this->faker->city,
                    'address_line_1' => $this->faker->streetName,
                    'address_line_2' => $this->faker->streetName,
                    'zip' => $this->faker->postcode,
                ],
                [
                    'name' => $this->faker->company,
                    'phone' => '38099940905',
                    'fax' => '38099940906',
                    'email' => $this->faker->unique()->safeEmail,
                    'receiving_persona' => $this->faker->userName,
                    'country_code' => $state_2->country->country_code,
                    'state_id' => $state_2->id,
                    'city' => $this->faker->city,
                    'address_line_1' => $this->faker->streetName,
                    'address_line_2' => $this->faker->streetName,
                    'zip' => $this->faker->postcode,
                ]
            ],
            'contact_account' => [
                'name' => $this->faker->sentence,
                'email' => $this->faker->safeEmail,
                'phone' => '38099990907',
                'country_code' => $state_1->country->country_code,
                'state_id' => $state_1->id,
                'city' => $this->faker->city,
                'address_line_1' => $this->faker->streetName,
                'address_line_2' => $this->faker->streetName,
                'zip' => $this->faker->postcode,
                'po_box' => $this->faker->postcode,
            ],
            'contact_order' => [
                'name' => $this->faker->sentence,
                'email' => $this->faker->safeEmail,
                'phone' => '38099990908',
                'country_code' => $state_2->country->country_code,
                'state_id' => $state_2->id,
                'city' => $this->faker->city,
                'address_line_1' => $this->faker->streetName,
                'address_line_2' => $this->faker->streetName,
                'zip' => $this->faker->postcode,
                'po_box' => $this->faker->postcode,
            ]
        ];
    }

    protected function getQueryStr(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s
                    company_info: {
                        type: %s
                        business_name: "%s"
                        email: "%s"
                        phone: "%s"
                        fax: "%s"
                        country_code: "%s"
                        state_id: %s
                        city: "%s"
                        address_line_1: "%s"
                        address_line_2: "%s"
                        zip: "%s"
                        po_box: "%s"
                        taxpayer_id: "%s"
                        tax: "%s"
                        websites: [
                            "%s", "%s", "%s"
                        ]
                        marketplaces: [
                            "%s", "%s"
                        ]
                        trade_names: []
                    }
                    shipping_address: [
                        {
                            id: "%s"
                            name: "%s"
                            active: %s
                            phone: "%s"
                            fax: "%s"
                            email: "%s"
                            receiving_persona: "%s"
                            country_code: "%s"
                            state_id: %s
                            city: "%s"
                            address_line_1: "%s"
                            address_line_2: "%s"
                            zip: "%s"
                        },
                        {
                            id: "%s"
                            name: "%s"
                            active: %s
                            phone: "%s"
                            fax: "%s"
                            email: "%s"
                            receiving_persona: "%s"
                            country_code: "%s"
                            state_id: %s
                            city: "%s"
                            address_line_1: "%s"
                            address_line_2: "%s"
                            zip: "%s"
                        },
                        {
                            name: "%s"
                            phone: "%s"
                            fax: "%s"
                            email: "%s"
                            receiving_persona: "%s"
                            country_code: "%s"
                            state_id: %s
                            city: "%s"
                            address_line_1: "%s"
                            address_line_2: "%s"
                            zip: "%s"
                        }
                    ]
                    contact_account: {
                        name: "%s"
                        email: "%s"
                        phone: "%s"
                        country_code: "%s"
                        state_id: %s
                        city: "%s"
                        address_line_1: "%s"
                        address_line_2: "%s"
                        zip: "%s"
                        po_box: "%s"
                    },
                    contact_order: {
                        name: "%s"
                        email: "%s"
                        phone: "%s"
                        country_code: "%s"
                        state_id: %s
                        city: "%s"
                        address_line_1: "%s"
                        address_line_2: "%s"
                        zip: "%s"
                        po_box: "%s"
                    },
                ) {
                    id
                    status
                    type
                    business_name
                    email
                    phone
                    fax
                    country {
                        country_code
                    }
                    state {
                        id
                    }
                    corporation {
                        id
                    }
                    city
                    address_line_1
                    address_line_2
                    zip
                    po_box
                    taxpayer_id
                    tax
                    websites
                    marketplaces
                    trade_names
                    media {
                        url
                    }
                    shipping_addresses {
                        id
                        name
                        active
                        phone
                        fax
                        email
                        receiving_persona
                        country {
                            country_code
                        }
                        state {
                            id
                        }
                        city
                        address_line_1
                        address_line_2
                        zip
                    }
                    contact_account {
                        name
                        phone
                        email
                        country {
                            country_code
                        }
                        state {
                            id
                        }
                        city
                        address_line_1
                        address_line_2
                        zip
                        po_box
                    }
                    contact_order {
                        name
                        phone
                        email
                        country {
                            country_code
                        }
                        state {
                            id
                        }
                        city
                        address_line_1
                        address_line_2
                        zip
                        po_box
                    }
                }
            }',
            self::MUTATION,
            data_get($data, 'id'),
            data_get($data, 'company_info.type'),
            data_get($data, 'company_info.business_name'),
            data_get($data, 'company_info.email'),
            data_get($data, 'company_info.phone'),
            data_get($data, 'company_info.fax'),
            data_get($data, 'company_info.country_code'),
            data_get($data, 'company_info.state_id'),
            data_get($data, 'company_info.city'),
            data_get($data, 'company_info.address_line_1'),
            data_get($data, 'company_info.address_line_2'),
            data_get($data, 'company_info.zip'),
            data_get($data, 'company_info.po_box'),
            data_get($data, 'company_info.taxpayer_id'),
            data_get($data, 'company_info.tax'),
            data_get($data, 'company_info.websites.0'),
            data_get($data, 'company_info.websites.1'),
            data_get($data, 'company_info.websites.2'),
            data_get($data, 'company_info.marketplaces.0'),
            data_get($data, 'company_info.marketplaces.1'),
            data_get($data, 'shipping_address.0.id'),
            data_get($data, 'shipping_address.0.name'),
            data_get($data, 'shipping_address.0.active'),
            data_get($data, 'shipping_address.0.phone'),
            data_get($data, 'shipping_address.0.fax'),
            data_get($data, 'shipping_address.0.email'),
            data_get($data, 'shipping_address.0.receiving_persona'),
            data_get($data, 'shipping_address.0.country_code'),
            data_get($data, 'shipping_address.0.state_id'),
            data_get($data, 'shipping_address.0.city'),
            data_get($data, 'shipping_address.0.address_line_1'),
            data_get($data, 'shipping_address.0.address_line_2'),
            data_get($data, 'shipping_address.0.zip'),
            data_get($data, 'shipping_address.1.id'),
            data_get($data, 'shipping_address.1.name'),
            data_get($data, 'shipping_address.1.active'),
            data_get($data, 'shipping_address.1.phone'),
            data_get($data, 'shipping_address.1.fax'),
            data_get($data, 'shipping_address.1.email'),
            data_get($data, 'shipping_address.1.receiving_persona'),
            data_get($data, 'shipping_address.1.country_code'),
            data_get($data, 'shipping_address.1.state_id'),
            data_get($data, 'shipping_address.1.city'),
            data_get($data, 'shipping_address.1.address_line_1'),
            data_get($data, 'shipping_address.1.address_line_2'),
            data_get($data, 'shipping_address.1.zip'),
            data_get($data, 'shipping_address.2.name'),
            data_get($data, 'shipping_address.2.phone'),
            data_get($data, 'shipping_address.2.fax'),
            data_get($data, 'shipping_address.2.email'),
            data_get($data, 'shipping_address.2.receiving_persona'),
            data_get($data, 'shipping_address.2.country_code'),
            data_get($data, 'shipping_address.2.state_id'),
            data_get($data, 'shipping_address.2.city'),
            data_get($data, 'shipping_address.2.address_line_1'),
            data_get($data, 'shipping_address.2.address_line_2'),
            data_get($data, 'shipping_address.2.zip'),
            data_get($data, 'contact_account.name'),
            data_get($data, 'contact_account.email'),
            data_get($data, 'contact_account.phone'),
            data_get($data, 'contact_account.country_code'),
            data_get($data, 'contact_account.state_id'),
            data_get($data, 'contact_account.city'),
            data_get($data, 'contact_account.address_line_1'),
            data_get($data, 'contact_account.address_line_2'),
            data_get($data, 'contact_account.zip'),
            data_get($data, 'contact_account.po_box'),
            data_get($data, 'contact_order.name'),
            data_get($data, 'contact_order.email'),
            data_get($data, 'contact_order.phone'),
            data_get($data, 'contact_order.country_code'),
            data_get($data, 'contact_order.state_id'),
            data_get($data, 'contact_order.city'),
            data_get($data, 'contact_order.address_line_1'),
            data_get($data, 'contact_order.address_line_2'),
            data_get($data, 'contact_order.zip'),
            data_get($data, 'contact_order.po_box'),
        );
    }

    protected function getQueryStrWithCorp(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s
                    company_info: {
                        corporation_id: %s
                        type: %s
                        business_name: "%s"
                        email: "%s"
                        phone: "%s"
                        fax: "%s"
                        country_code: "%s"
                        state_id: %s
                        city: "%s"
                        address_line_1: "%s"
                        address_line_2: "%s"
                        zip: "%s"
                        po_box: "%s"
                        taxpayer_id: "%s"
                        tax: "%s"
                        websites: []
                        marketplaces: []
                        trade_names: []
                    }
                    contact_account: {
                        name: "%s"
                        email: "%s"
                        phone: "%s"
                        country_code: "%s"
                        state_id: %s
                        city: "%s"
                        address_line_1: "%s"
                        address_line_2: "%s"
                        zip: "%s"
                        po_box: "%s"
                    },
                    contact_order: {
                        name: "%s"
                        email: "%s"
                        phone: "%s"
                        country_code: "%s"
                        state_id: %s
                        city: "%s"
                        address_line_1: "%s"
                        address_line_2: "%s"
                        zip: "%s"
                        po_box: "%s"
                    },
                ) {
                    id
                    corporation {
                        id
                    }
                }
            }',
            self::MUTATION,
            data_get($data, 'id'),
            data_get($data, 'company_info.corporation_id'),
            data_get($data, 'company_info.type'),
            data_get($data, 'company_info.business_name'),
            data_get($data, 'company_info.email'),
            data_get($data, 'company_info.phone'),
            data_get($data, 'company_info.fax'),
            data_get($data, 'company_info.country_code'),
            data_get($data, 'company_info.state_id'),
            data_get($data, 'company_info.city'),
            data_get($data, 'company_info.address_line_1'),
            data_get($data, 'company_info.address_line_2'),
            data_get($data, 'company_info.zip'),
            data_get($data, 'company_info.po_box'),
            data_get($data, 'company_info.taxpayer_id'),
            data_get($data, 'company_info.tax'),
            data_get($data, 'contact_account.name'),
            data_get($data, 'contact_account.email'),
            data_get($data, 'contact_account.phone'),
            data_get($data, 'contact_account.country_code'),
            data_get($data, 'contact_account.state_id'),
            data_get($data, 'contact_account.city'),
            data_get($data, 'contact_account.address_line_1'),
            data_get($data, 'contact_account.address_line_2'),
            data_get($data, 'contact_account.zip'),
            data_get($data, 'contact_account.po_box'),
            data_get($data, 'contact_order.name'),
            data_get($data, 'contact_order.email'),
            data_get($data, 'contact_order.phone'),
            data_get($data, 'contact_order.country_code'),
            data_get($data, 'contact_order.state_id'),
            data_get($data, 'contact_order.city'),
            data_get($data, 'contact_order.address_line_1'),
            data_get($data, 'contact_order.address_line_2'),
            data_get($data, 'contact_order.zip'),
            data_get($data, 'contact_order.po_box'),
        );
    }
}
