<?php

namespace Tests\Feature\Mutations\FrontOffice\Companies;

use App\Enums\Companies\CompanyStatus;
use App\Enums\Companies\CompanyType;
use App\Events\Companies\CreateOrUpdateCompanyEvent;
use App\GraphQL\Mutations\FrontOffice\Companies;
use App\Listeners\Companies\SendDataToOnecListeners;
use App\Models\Companies\Company;
use App\Models\Dealers\Dealer;
use App\Models\Locations\State;
use App\Models\Media\Media;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Company\CompanyBuilder;
use Tests\TestCase;

class CreateApplicationMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = Companies\CreateApplicationMutation::NAME;

    protected CompanyBuilder $companyBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyBuilder = resolve(CompanyBuilder::class);
    }

    /** @test */
    public function success_create(): void
    {
        Event::fake([CreateOrUpdateCompanyEvent::class]);

        $data = $this->data();

        $id = $this->postGraphQL([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'status' => CompanyStatus::DRAFT(),
                        'type' => data_get($data, 'company_info.type'),
                        'business_name' => data_get($data, 'company_info.business_name'),
                        'email' => data_get($data, 'company_info.email'),
                        'phone' => data_get($data, 'company_info.phone'),
                        'fax' => data_get($data, 'company_info.fax'),
                        'country' => [
                            'country_code' => data_get($data, 'company_info.country_code')
                        ],
                        'state' => [
                            'id' => data_get($data, 'company_info.state_id')
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
                        'trade_names' => data_get($data, 'company_info.trade_names'),
                        'shipping_addresses' => [
                            [
                                'name' => data_get($data, 'shipping_address.0.name'),
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
                    ]
                ]
            ])
            ->assertJsonCount(0, 'data.'.self::MUTATION.'.media')
            ->json('data.'.self::MUTATION.'.id')
        ;

        /** @var $model Company */
        $model = Company::find($id);

        $this->assertNull($model->code);
        $this->assertNull($model->terms);

        Event::assertDispatched(function (CreateOrUpdateCompanyEvent $event) use ($model) {
            return $event->getCompany()->id === $model->id;
        });
        Event::assertListening(CreateOrUpdateCompanyEvent::class, SendDataToOnecListeners::class);
    }

    public function data(): array
    {
        list(
            $state_1,
            $state_2,
            $state_3,
            $state_4,
            ) = State::with('country')->limit(4)->get();

        return [
            'company_info' => [
                'type' => CompanyType::CORPORATION(),
                'business_name' => $this->faker->company,
                'email' => $this->faker->safeEmail,
                'phone' => '38099990901',
                'fax' => '38099990902',
                'country_code' => $state_1->country->country_code,
                'state_id' => $state_1->id,
                'city' => $this->faker->city,
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
                    'name' => $this->faker->company,
                    'active' => true,
                    'phone' => '38099990903',
                    'fax' => '38099990904',
                    'email' => $this->faker->unique->safeEmail,
                    'receiving_persona' => $this->faker->userName,
                    'country_code' => $state_1->country->country_code,
                    'state_id' => $state_1->id,
                    'city' => $this->faker->city,
                    'address_line_1' => $this->faker->streetName,
                    'address_line_2' => $this->faker->streetName,
                    'zip' => $this->faker->postcode,
                ],
                [
                    'name' => $this->faker->company,
                    'active' => true,
                    'phone' => '38099990905',
                    'fax' => '38099990906',
                    'email' => $this->faker->unique->safeEmail,
                    'receiving_persona' => $this->faker->userName,
                    'country_code' => $state_4->country->country_code,
                    'state_id' => $state_4->id,
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
                        trade_names: [
                            "%s", "%s"
                        ]
                    }
                    shipping_address: [
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
                    }
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
            data_get($data, 'company_info.trade_names.0'),
            data_get($data, 'company_info.trade_names.1'),
            data_get($data, 'shipping_address.0.name'),
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
            data_get($data, 'shipping_address.1.name'),
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

    /** @test */
    public function create_only_required_field_and_with_files(): void
    {
        $data = $this->data();

        $file_1 = UploadedFile::fake()->image('file_1.jpg');
        $file_2 = UploadedFile::fake()->image('file_2.pdf');

        $attributes = [
            'operations' => sprintf(
                '{"query": "mutation ($media: [Upload!]) {%s (company_info: {business_name: \"%s\", email: \"%s\", phone: \"%s\", country_code: \"%s\", state_id: \"%s\", city: \"%s\", address_line_1: \"%s\", zip: \"%s\", type: %s, taxpayer_id: \"%s\"}, contact_account:{name:\"%s\",phone:\"%s\",email:\"%s\",country_code:\"%s\",state_id:\"%s\",city:\"%s\",address_line_1:\"%s\",zip:\"%s\"}, contact_order:{name:\"%s\",phone:\"%s\",email:\"%s\",country_code:\"%s\",state_id:\"%s\",city:\"%s\",address_line_1:\"%s\",zip:\"%s\"}, media: $media) {id, media{url}, shipping_addresses{id}} }"}',
                self::MUTATION,
                data_get($data, 'company_info.business_name'),
                data_get($data, 'company_info.email'),
                data_get($data, 'company_info.phone'),
                data_get($data, 'company_info.country_code'),
                data_get($data, 'company_info.state_id'),
                data_get($data, 'company_info.city'),
                data_get($data, 'company_info.address_line_1'),
                data_get($data, 'company_info.zip'),
                data_get($data, 'company_info.type'),
                data_get($data, 'company_info.taxpayer_id'),
                data_get($data, 'contact_account.name'),
                data_get($data, 'contact_account.phone'),
                data_get($data, 'contact_account.email'),
                data_get($data, 'contact_account.country_code'),
                data_get($data, 'contact_account.state_id'),
                data_get($data, 'contact_account.city'),
                data_get($data, 'contact_account.address_line_1'),
                data_get($data, 'contact_account.zip'),
                data_get($data, 'contact_order.name'),
                data_get($data, 'contact_order.phone'),
                data_get($data, 'contact_order.email'),
                data_get($data, 'contact_order.country_code'),
                data_get($data, 'contact_order.state_id'),
                data_get($data, 'contact_order.city'),
                data_get($data, 'contact_order.address_line_1'),
                data_get($data, 'contact_order.zip'),
            ),
            'map' => '{ "media": ["variables.media"] }',
            'media' => [$file_1, $file_2],
        ];
        $id = $this->postGraphQlUpload($attributes)
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'shipping_addresses' => []
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.'.self::MUTATION.'.media')
            ->assertJsonCount(0, 'data.'.self::MUTATION.'.shipping_addresses')
            ->json('data.'.self::MUTATION.'.id')
        ;

        $this->assertDatabaseCount(Media::TABLE, 2);
        $this->assertDatabaseHas(Media::TABLE, ['model_type' => Company::MORPH_NAME, 'model_id' => $id]);
    }

    /** @test */
    public function validate_uniq_phone(): void
    {
        $phone = '167676767';
        /** @var $company Company */
        $this->companyBuilder->setData([
            'phone' => new Phone($phone)
        ])->create();

        $data = $this->data();
        $data['company_info']['phone'] = '+1-6767-6767';

        $this->postGraphQL([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                    [
                        "message" => "validation",
                        "extensions" => [
                            "validation" => [
                                "company_info.phone" => ["The company info.phone has already been taken."]
                            ]
                        ]
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function validate_wrong_phone(): void
    {
        $phone = '167676767';
        /** @var $company Company */
        $this->companyBuilder->setData([
            'phone' => new Phone($phone)
        ])->create();

        $data = $this->data();
        $data['company_info']['phone'] = '+1-6767-6767_';

        $this->postGraphQL([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                    [
                        "message" => "validation",
                        "extensions" => [
                            "validation" => [
                                "company_info.phone" => ["The company info.phone has already been taken."]
                            ]
                        ]
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function validate_uniq_fax(): void
    {
        $fax = '167676767';
        /** @var $company Company */
        $this->companyBuilder->setData([
            'fax' => new Phone($fax)
        ])->create();

        $data = $this->data();
        $data['company_info']['fax'] = '+1-6767-6767';

        $this->postGraphQL([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                    [
                        "message" => "validation",
                        "extensions" => [
                            "validation" => [
                                "company_info.fax" => ["The company info.fax has already been taken."]
                            ]
                        ]
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function fail_exist_email_to_user(): void
    {
        $email = 'test@test.com';
        User::factory()->create([
            'email' => new Email($email)
        ]);

        $data = $this->data();
        $data['company_info']['email'] = $email;

        $this->postGraphQL([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                    [
                        "message" => "validation",
                        "extensions" => [
                            "validation" => [
                                "company_info.email" => [__('validation.unique', ['attribute' => 'email'])]
                            ]
                        ]
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function fail_exist_email_to_tech(): void
    {
        $email = 'test@test.com';
        Technician::factory()->create([
            'email' => new Email($email)
        ]);

        $data = $this->data();
        $data['company_info']['email'] = $email;

        $this->postGraphQL([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                    [
                        "message" => "validation",
                        "extensions" => [
                            "validation" => [
                                "company_info.email" => [__('validation.unique', ['attribute' => 'email'])]
                            ]
                        ]
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function fail_exist_email_to_dealer(): void
    {
        $email = 'test@test.com';
        Dealer::factory()->create([
            'email' => new Email($email)
        ]);

        $data = $this->data();
        $data['company_info']['email'] = $email;

        $this->postGraphQL([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                    [
                        "message" => "validation",
                        "extensions" => [
                            "validation" => [
                                "company_info.email" => [__('validation.unique', ['attribute' => 'email'])]
                            ]
                        ]
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function fail_exist_email_to_company(): void
    {
        $email = 'test@test.com';
        Company::factory()->create([
            'email' => new Email($email)
        ]);

        $data = $this->data();
        $data['company_info']['email'] = $email;

        $this->postGraphQL([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                    [
                        "message" => "validation",
                        "extensions" => [
                            "validation" => [
                                "company_info.email" => [__('validation.unique', ['attribute' => 'email'])]
                            ]
                        ]
                    ]
                ]
            ])
        ;
    }
}
