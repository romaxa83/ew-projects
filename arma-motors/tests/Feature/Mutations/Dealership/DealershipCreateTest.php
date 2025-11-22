<?php

namespace Tests\Feature\Mutations\Dealership;

use App\Events\ChangeHashEvent;
use App\Exceptions\ErrorsCode;
use App\Models\Catalogs\Car\Brand;
use App\Models\Hash;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Statuses;

class DealershipCreateTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use Statuses;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function create_success()
    {
        \Event::fake([ChangeHashEvent::class]);

        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::DEALERSHIP_CREATE)
            ->create();
        $this->loginAsAdmin($admin);

        $brand = Brand::orderBy(\DB::raw('RAND()'))->first();

        $data = $this->data($brand->id, true, true);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.dealershipCreate');

        $locale = \App::getLocale();

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('website', $responseData);
        $this->assertArrayHasKey('location', $responseData);
        $this->assertArrayHasKey('name', $responseData['current']);
        $this->assertArrayHasKey('lang', $responseData['current']);
        $this->assertArrayHasKey('text', $responseData['current']);
        $this->assertArrayHasKey('address', $responseData['current']);
        $this->assertCount(2, $responseData['translations']);
        $this->assertArrayHasKey('name', $responseData['translations'][0]);
        $this->assertArrayHasKey('lang', $responseData['translations'][0]);
        $this->assertArrayHasKey('id', $responseData['brand']);
        $this->assertArrayHasKey('name', $responseData['brand']);

        $this->assertArrayHasKey('timeStep', $responseData);
        $this->assertCount(2, $responseData['timeStep']);
        $this->assertArrayHasKey('step', $responseData['timeStep'][0]);
        $this->assertArrayHasKey('service', $responseData['timeStep'][0]);
        $this->assertArrayHasKey('id', $responseData['timeStep'][0]['service']);

        $this->assertArrayHasKey('id', $responseData['departmentSales']);
        $this->assertArrayHasKey('active', $responseData['departmentSales']);
        $this->assertArrayHasKey('phone', $responseData['departmentSales']);
        $this->assertArrayHasKey('email', $responseData['departmentSales']);
        $this->assertArrayHasKey('viber', $responseData['departmentSales']);
        $this->assertArrayHasKey('telegram', $responseData['departmentSales']);
        $this->assertArrayHasKey('current', $responseData['departmentSales']);
        $this->assertArrayHasKey('lang', $responseData['departmentSales']['current']);
        $this->assertArrayHasKey('name', $responseData['departmentSales']['current']);
        $this->assertArrayHasKey('address', $responseData['departmentSales']['current']);
        $this->assertEquals($responseData['departmentSales']['email'], $data['departmentSales']['email']);
        $this->assertEquals($responseData['departmentSales']['telegram'], $data['departmentSales']['telegram']);
        $this->assertArrayHasKey('schedule', $responseData['departmentSales']);
        $this->assertCount(7, $responseData['departmentSales']['schedule']);
        $this->assertArrayHasKey('day', $responseData['departmentSales']['schedule'][0]);
        $this->assertArrayHasKey('from', $responseData['departmentSales']['schedule'][0]);
        $this->assertArrayHasKey('to', $responseData['departmentSales']['schedule'][0]);
        $this->assertArrayHasKey('time', $responseData['departmentSales']['schedule'][0]);
        $this->assertEquals($responseData['departmentSales']['schedule'][3]['day'],$data['departmentSales']['schedule'][3]['day']);
        $this->assertEquals($responseData['departmentSales']['schedule'][3]['from'],$data['departmentSales']['schedule'][3]['from']);
        $this->assertEquals($responseData['departmentSales']['schedule'][3]['to'],$data['departmentSales']['schedule'][3]['to']);

        $this->assertArrayHasKey('id', $responseData['departmentService']);
        $this->assertArrayHasKey('active', $responseData['departmentService']);
        $this->assertArrayHasKey('phone', $responseData['departmentService']);
        $this->assertArrayHasKey('email', $responseData['departmentService']);
        $this->assertArrayHasKey('viber', $responseData['departmentService']);
        $this->assertArrayHasKey('telegram', $responseData['departmentService']);
        $this->assertArrayHasKey('current', $responseData['departmentService']);
        $this->assertArrayHasKey('lang', $responseData['departmentService']['current']);
        $this->assertArrayHasKey('name', $responseData['departmentService']['current']);
        $this->assertArrayHasKey('address', $responseData['departmentService']['current']);
        $this->assertEquals($responseData['departmentService']['email'], $data['departmentService']['email']);
        $this->assertEquals($responseData['departmentService']['telegram'], $data['departmentService']['telegram']);

        $this->assertArrayHasKey('id', $responseData['departmentCash']);
        $this->assertArrayHasKey('active', $responseData['departmentCash']);
        $this->assertArrayHasKey('phone', $responseData['departmentCash']);
        $this->assertArrayHasKey('email', $responseData['departmentCash']);
        $this->assertArrayHasKey('viber', $responseData['departmentCash']);
        $this->assertArrayHasKey('telegram', $responseData['departmentCash']);
        $this->assertArrayHasKey('current', $responseData['departmentCash']);
        $this->assertArrayHasKey('lang', $responseData['departmentCash']['current']);
        $this->assertArrayHasKey('name', $responseData['departmentCash']['current']);
        $this->assertArrayHasKey('address', $responseData['departmentCash']['current']);
        $this->assertEquals($responseData['departmentCash']['email'], $data['departmentCash']['email']);
        $this->assertEquals($responseData['departmentCash']['telegram'], $data['departmentCash']['telegram']);

        $this->assertArrayHasKey('id', $responseData['departmentBody']);
        $this->assertArrayHasKey('active', $responseData['departmentBody']);
        $this->assertArrayHasKey('phone', $responseData['departmentBody']);
        $this->assertArrayHasKey('email', $responseData['departmentBody']);
        $this->assertArrayHasKey('viber', $responseData['departmentBody']);
        $this->assertArrayHasKey('telegram', $responseData['departmentBody']);
        $this->assertArrayHasKey('current', $responseData['departmentBody']);
        $this->assertArrayHasKey('lang', $responseData['departmentBody']['current']);
        $this->assertArrayHasKey('name', $responseData['departmentBody']['current']);
        $this->assertArrayHasKey('address', $responseData['departmentBody']['current']);
        $this->assertEquals($responseData['departmentBody']['email'], $data['departmentBody']['email']);
        $this->assertEquals($responseData['departmentBody']['telegram'], $data['departmentBody']['telegram']);

        $this->assertTrue($responseData['active']);
        $this->assertEquals($responseData['website'], $data['website']);
        $this->assertEquals($responseData['current']['lang'], $locale);
        $this->assertEquals($responseData['current']['name'], $data['translations'][$locale]['name']);
        $this->assertEquals($responseData['current']['text'], $data['translations'][$locale]['text']);
        $this->assertEquals($responseData['current']['address'], $data['translations'][$locale]['address']);
        $this->assertEquals($responseData['brand']['id'], $brand->id);
        $this->assertEquals($responseData['brand']['name'], $brand->name);

        \Event::assertDispatched(function (ChangeHashEvent $event){
            return $event->alias == Hash::ALIAS_DEALERSHIP;
        });
    }

    /** @test */
    public function not_auth()
    {
        $this->adminBuilder()
            ->createRoleWithPerm(Permissions::DEALERSHIP_CREATE)
            ->create();

        $brand = Brand::orderBy(\DB::raw('RAND()'))->first();
        $data = $this->data($brand->id, true, true);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::DEALERSHIP_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $brand = Brand::orderBy(\DB::raw('RAND()'))->first();
        $data = $this->data($brand->id, true, true);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    public static function data(
        $brandId,
        $withDepartments = true,
        $withTimeStep = false
    ): array
    {
        $data = [
            'brandId' => $brandId,
            'website' => 'http://some-site.com',
            'lat' => '55.909090',
            'lon' => '56.909090',
            'alias' => 'dddd',
            'translations' => [
                'ru' => [
                    'lang' => 'ru',
                    'name' => 'some name ru',
                    'text' => 'some text ru',
                    'address' => 'some address ru',
                ],
                'uk' => [
                    'lang' => 'uk',
                    'name' => 'some name uk',
                    'text' => 'some text uk',
                    'address' => 'some address uk',
                ],
            ]
        ];

        if($withDepartments){
            $data['departmentSales'] = [
                'phone' => '+38955555551',
                'email' => 'department.1@some.com',
                'telegram' => 'telegram_1',
                'viber' => 'viber_1',
                'lat' => '55.909090',
                'lon' => '56.909090',
                'translations' => [
                    'ru' => [
                        'lang' => 'ru',
                        'name' => 'отдел продаж',
                        'address' => 'some_address'
                    ],
                    'uk' => [
                        'lang' => 'uk',
                        'name' => 'отдел продаж',
                        'address' => 'some_address'
                    ],
                ],
                'schedule' => static::schedule()
            ];
            $data['departmentService'] = [
                'phone' => '+38955555551',
                'email' => 'department.2@some.com',
                'telegram' => 'telegram_2',
                'viber' => 'viber_2',
                'lat' => '55.909090',
                'lon' => '56.909090',
                'translations' => [
                    'ru' => [
                        'lang' => 'ru',
                        'name' => 'сервисный отдел',
                        'address' => 'some_address'
                    ],
                    'uk' => [
                        'lang' => 'uk',
                        'name' => 'сервисный отдел',
                        'address' => 'some_address'
                    ],
                ],
                'schedule' => static::schedule()
            ];
            $data['departmentCash'] = [
                'phone' => '+38955555551',
                'email' => 'department.3@some.com',
                'telegram' => 'telegram_3',
                'viber' => 'viber_3',
                'lat' => '55.909090',
                'lon' => '56.909090',
                'translations' => [
                    'ru' => [
                        'lang' => 'ru',
                        'name' => 'отдел страхования и кредитования',
                        'address' => 'some_address'
                    ],
                    'uk' => [
                        'lang' => 'uk',
                        'name' => 'отдел страхования и кредитования',
                        'address' => 'some_address'
                    ],
                ],
                'schedule' => static::schedule()
            ];
            $data['departmentBody'] = [
                'phone' => '+38955555551',
                'email' => 'department.4@some.com',
                'telegram' => 'telegram_4',
                'viber' => 'viber_4',
                'lat' => '55.909090',
                'lon' => '56.909090',
                'translations' => [
                    'ru' => [
                        'lang' => 'ru',
                        'name' => 'кузовной одел',
                        'address' => 'some_address'
                    ],
                    'uk' => [
                        'lang' => 'uk',
                        'name' => 'кузовной одел',
                        'address' => 'some_address'
                    ],
                ],
                'schedule' => static::schedule()
            ];
        }

        if($withTimeStep){
            $data['timeStep'] = [
                [
                    "serviceId" => 6,
                    "step" => 3600000,
                ],
                [
                    "serviceId" => 7,
                    "step" => 7200000,
                ]
            ];
        }

        return $data;
    }

    public static function schedule(): array
    {
        return [
            [
                'day' => 1,
                'from' => 28800000,
                'to' => 61200000
            ],
            [
                'day' => 2,
                'from' => 28800000,
                'to' => 61200000
            ],
            [
                'day' => 3,
                'from' => 28800000,
                'to' => 61200000
            ],
            [
                'day' => 4,
                'from' => 48800000,
                'to' => 67200000
            ],
            [
                'day' => 5,
                'from' => 28800000,
                'to' => 61200000
            ],
            [
                'day' => 6,
                'from' => 28800000,
                'to' => 61200000
            ],
            [
                'day' => 7,
                'from' => null,
                'to' => null
            ],
        ];
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                dealershipCreate(input:{
                    brandId: "%s",
                    website: "%s",
                    lat: "%s",
                    lon: "%s",
                    translations: [
                        {lang: "%s", name: "%s", text: "%s", address: "%s"}
                        {lang: "%s", name: "%s", text: "%s", address: "%s"}
                    ],
                    timeStep: [
                        {serviceId: "%s", step: %d}
                        {serviceId: "%s", step: %d}
                    ],
                    departmentSales: {
                        phone: "%s",
                        email: "%s",
                        telegram: "%s",
                        viber: "%s",
                        lat: "%s",
                        lon: "%s",
                        translations: [
                            {lang: "%s", name: "%s", address: "%s"}
                            {lang: "%s", name: "%s", address: "%s"}
                        ],
                        schedule: [
                            {day: %d, from: %d, to: %d}
                            {day: %d, from: %d, to: %d}
                            {day: %d, from: %d, to: %d}
                            {day: %d, from: %d, to: %d}
                            {day: %d, from: %d, to: %d}
                            {day: %d, from: %d, to: %d}
                            {day: 7, from: null, to: null}
                        ]
                    },
                    departmentService: {
                        phone: "%s",
                        email: "%s",
                        telegram: "%s",
                        viber: "%s",
                        lat: "%s",
                        lon: "%s",
                        translations: [
                            {lang: "%s", name: "%s", address: "%s"}
                            {lang: "%s", name: "%s", address: "%s"}
                        ],
                        schedule: [
                            {day: 1, from: 28800000, to: 61200000}
                            {day: 2, from: 28800000, to: 61200000}
                            {day: 3, from: 28800000, to: 61200000}
                            {day: 4, from: 28800000, to: 61200000}
                            {day: 5, from: 28800000, to: 61200000}
                            {day: 6, from: 28800000, to: 61200000}
                            {day: 7, from: null, to: null}
                        ]
                    },
                    departmentCash: {
                        phone: "%s",
                        email: "%s",
                        telegram: "%s",
                        viber: "%s",
                        lat: "%s",
                        lon: "%s",
                        translations: [
                            {lang: "%s", name: "%s", address: "%s"}
                            {lang: "%s", name: "%s", address: "%s"}
                        ],
                        schedule: [
                            {day: 1, from: 28800000, to: 61200000}
                            {day: 2, from: 28800000, to: 61200000}
                            {day: 3, from: 28800000, to: 61200000}
                            {day: 4, from: 28800000, to: 61200000}
                            {day: 5, from: 28800000, to: 61200000}
                            {day: 6, from: 28800000, to: 61200000}
                            {day: 7, from: null, to: null}
                        ]
                    },
                    departmentBody: {
                        phone: "%s",
                        email: "%s",
                        telegram: "%s",
                        viber: "%s",
                        lat: "%s",
                        lon: "%s",
                        translations: [
                            {lang: "%s", name: "%s", address: "%s"}
                            {lang: "%s", name: "%s", address: "%s"}
                        ],
                        schedule: [
                            {day: 1, from: 28800000, to: 61200000}
                            {day: 2, from: 28800000, to: 61200000}
                            {day: 3, from: 28800000, to: 61200000}
                            {day: 4, from: 28800000, to: 61200000}
                            {day: 5, from: 28800000, to: 61200000}
                            {day: 6, from: 28800000, to: 61200000}
                            {day: 7, from: null, to: null}
                        ]
                    },
                }) {
                    id
                    active
                    sort
                    website
                    location
                    current {
                        lang
                        name
                        text
                        address
                    }
                    translations {
                        name
                        lang
                    }
                    brand {
                        id
                        name
                    }
                    timeStep {
                        id
                        step
                        service {
                            id
                        }
                    }
                    departmentSales {
                        id
                        active
                        phone
                        email
                        viber
                        telegram
                        location
                        current {
                            lang
                            name
                            address
                        }
                        schedule {
                            day
                            from
                            to
                            time
                        }
                    },
                    departmentService {
                        id
                        active
                        phone
                        email
                        viber
                        telegram
                        location
                        current {
                            lang
                            name
                            address
                        }
                    },
                    departmentCash {
                        id
                        active
                        phone
                        email
                        viber
                        telegram
                        location
                        current {
                            lang
                            name
                            address
                        }
                    },
                    departmentBody {
                        id
                        active
                        phone
                        email
                        viber
                        telegram
                        location
                        current {
                            lang
                            name
                            address
                        }
                    }
                }
            }',
            $data['brandId'],
            $data['website'],
            $data['lat'],
            $data['lon'],
            $data['translations']['ru']['lang'],
            $data['translations']['ru']['name'],
            $data['translations']['ru']['text'],
            $data['translations']['ru']['address'],
            $data['translations']['uk']['lang'],
            $data['translations']['uk']['name'],
            $data['translations']['uk']['text'],
            $data['translations']['uk']['address'],
            $data['timeStep'][0]['serviceId'],
            $data['timeStep'][0]['step'],
            $data['timeStep'][1]['serviceId'],
            $data['timeStep'][1]['step'],
            $data['departmentSales']['phone'],
            $data['departmentSales']['email'],
            $data['departmentSales']['telegram'],
            $data['departmentSales']['viber'],
            $data['departmentSales']['lat'],
            $data['departmentSales']['lon'],
            $data['departmentSales']['translations']['ru']['lang'],
            $data['departmentSales']['translations']['ru']['name'],
            $data['departmentSales']['translations']['ru']['address'],
            $data['departmentSales']['translations']['uk']['lang'],
            $data['departmentSales']['translations']['uk']['name'],
            $data['departmentSales']['translations']['uk']['address'],
            $data['departmentSales']['schedule'][0]['day'],
            $data['departmentSales']['schedule'][0]['from'],
            $data['departmentSales']['schedule'][0]['to'],
            $data['departmentSales']['schedule'][1]['day'],
            $data['departmentSales']['schedule'][1]['from'],
            $data['departmentSales']['schedule'][1]['to'],
            $data['departmentSales']['schedule'][2]['day'],
            $data['departmentSales']['schedule'][2]['from'],
            $data['departmentSales']['schedule'][2]['to'],
            $data['departmentSales']['schedule'][3]['day'],
            $data['departmentSales']['schedule'][3]['from'],
            $data['departmentSales']['schedule'][3]['to'],
            $data['departmentSales']['schedule'][4]['day'],
            $data['departmentSales']['schedule'][4]['from'],
            $data['departmentSales']['schedule'][4]['to'],
            $data['departmentSales']['schedule'][5]['day'],
            $data['departmentSales']['schedule'][5]['from'],
            $data['departmentSales']['schedule'][5]['to'],
            $data['departmentService']['phone'],
            $data['departmentService']['email'],
            $data['departmentService']['telegram'],
            $data['departmentService']['viber'],
            $data['departmentService']['lat'],
            $data['departmentService']['lon'],
            $data['departmentService']['translations']['ru']['lang'],
            $data['departmentService']['translations']['ru']['name'],
            $data['departmentService']['translations']['ru']['address'],
            $data['departmentService']['translations']['uk']['lang'],
            $data['departmentService']['translations']['uk']['name'],
            $data['departmentService']['translations']['uk']['address'],
            $data['departmentCash']['phone'],
            $data['departmentCash']['email'],
            $data['departmentCash']['telegram'],
            $data['departmentCash']['viber'],
            $data['departmentCash']['lat'],
            $data['departmentCash']['lon'],
            $data['departmentCash']['translations']['ru']['lang'],
            $data['departmentCash']['translations']['ru']['name'],
            $data['departmentCash']['translations']['ru']['address'],
            $data['departmentCash']['translations']['uk']['lang'],
            $data['departmentCash']['translations']['uk']['name'],
            $data['departmentCash']['translations']['uk']['address'],
            $data['departmentBody']['phone'],
            $data['departmentBody']['email'],
            $data['departmentBody']['telegram'],
            $data['departmentBody']['viber'],
            $data['departmentBody']['lat'],
            $data['departmentBody']['lon'],
            $data['departmentBody']['translations']['ru']['lang'],
            $data['departmentBody']['translations']['ru']['name'],
            $data['departmentBody']['translations']['ru']['address'],
            $data['departmentBody']['translations']['uk']['lang'],
            $data['departmentBody']['translations']['uk']['name'],
            $data['departmentBody']['translations']['uk']['address']
        );
    }

    private function getQueryStrWithoutDepartments(array $data): string
    {
        return sprintf('
            mutation {
                dealershipCreate(input:{
                    brandId: "%s",
                    website: "%s",
                    lat: "%s",
                    lon: "%s",
                    translations: [
                        {lang: "%s", name: "%s", text: "%s", address: "%s"}
                        {lang: "%s", name: "%s", text: "%s", address: "%s"}
                    ],
                }) {
                    id
                    active
                    sort
                    website
                    location
                    current {
                        lang
                        name
                        text
                        address
                    }
                    translations {
                        name
                        lang
                    }
                    brand {
                        id
                        name
                    }
                    departments {
                        id
                    }
                }
            }',
            $data['brandId'],
            $data['website'],
            $data['lat'],
            $data['lon'],
            $data['translations'][0]['lang'],
            $data['translations'][0]['name'],
            $data['translations'][0]['text'],
            $data['translations'][0]['address'],
            $data['translations'][1]['lang'],
            $data['translations'][1]['name'],
            $data['translations'][1]['text'],
            $data['translations'][1]['address'],
        );
    }
}

