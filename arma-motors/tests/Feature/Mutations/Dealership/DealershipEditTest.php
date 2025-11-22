<?php

namespace Tests\Feature\Mutations\Dealership;

use App\Events\ChangeHashEvent;
use App\Exceptions\ErrorsCode;
use App\Models\Catalogs\Car\Brand;
use App\Models\Dealership\Dealership;
use App\Models\Hash;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Statuses;

class DealershipEditTest extends TestCase
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
    public function edit_success()
    {
        \Event::fake([ChangeHashEvent::class]);

        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::DEALERSHIP_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $brand = Brand::where('id',1)->first();
        $data = DealershipCreateTest::data($brand->id);
        $dealership = Dealership::where('id',1)->first();
        $data['id'] = $dealership->id;

        $this->assertNotEquals($dealership->website, $data['website']);
        $this->assertNotEquals($dealership->current->name, $data['translations'][$dealership->current->lang]['name']);
        $this->assertNotEquals($dealership->current->text, $data['translations'][$dealership->current->lang]['text']);
        $this->assertNotEquals($dealership->current->address, $data['translations'][$dealership->current->lang]['address']);

        $this->assertNotEquals($dealership->departmentSales->email, $data['departmentSales']['email']);
        $this->assertNotEquals($dealership->departmentSales->telegram, $data['departmentSales']['telegram']);
        $this->assertNotEquals($dealership->departmentSales->viber, $data['departmentSales']['viber']);
        $this->assertNotEquals($dealership->departmentSales->current->name, $data['departmentSales']['translations'][$dealership->departmentSales->current->lang]['name']);
        $this->assertNotEquals($dealership->departmentSales->current->address, $data['departmentSales']['translations'][$dealership->departmentSales->current->lang]['address']);

        $this->assertNotEquals($dealership->departmentService->email, $data['departmentService']['email']);
        $this->assertNotEquals($dealership->departmentService->telegram, $data['departmentService']['telegram']);
        $this->assertNotEquals($dealership->departmentService->viber, $data['departmentService']['viber']);
        $this->assertNotEquals($dealership->departmentService->current->name, $data['departmentService']['translations'][$dealership->departmentSales->current->lang]['name']);
        $this->assertNotEquals($dealership->departmentService->current->address, $data['departmentService']['translations'][$dealership->departmentSales->current->lang]['address']);

        $this->assertNotEquals($dealership->departmentCash->email, $data['departmentCash']['email']);
        $this->assertNotEquals($dealership->departmentCash->telegram, $data['departmentCash']['telegram']);
        $this->assertNotEquals($dealership->departmentCash->viber, $data['departmentCash']['viber']);
        $this->assertNotEquals($dealership->departmentCash->current->name, $data['departmentCash']['translations'][$dealership->departmentSales->current->lang]['name']);
        $this->assertNotEquals($dealership->departmentCash->current->address, $data['departmentCash']['translations'][$dealership->departmentSales->current->lang]['address']);

        $this->assertNotEquals($dealership->departmentBody->email, $data['departmentBody']['email']);
        $this->assertNotEquals($dealership->departmentBody->telegram, $data['departmentBody']['telegram']);
        $this->assertNotEquals($dealership->departmentBody->viber, $data['departmentBody']['viber']);
        $this->assertNotEquals($dealership->departmentBody->current->name, $data['departmentBody']['translations'][$dealership->departmentSales->current->lang]['name']);
        $this->assertNotEquals($dealership->departmentBody->current->address, $data['departmentBody']['translations'][$dealership->departmentSales->current->lang]['address']);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.dealershipEdit');

        $dealership->refresh();

        $this->assertEquals($responseData['id'], $dealership->id);
        $this->assertEquals($responseData['website'], $data['website']);
        $this->assertEquals($dealership->website, $data['website']);
        $this->assertEquals($dealership->current->name, $data['translations'][$dealership->current->lang]['name']);
        $this->assertEquals($dealership->current->text, $data['translations'][$dealership->current->lang]['text']);
        $this->assertEquals($dealership->current->address, $data['translations'][$dealership->current->lang]['address']);

        $this->assertEquals($dealership->departmentSales->email, $data['departmentSales']['email']);
        $this->assertEquals($dealership->departmentSales->telegram, $data['departmentSales']['telegram']);
        $this->assertEquals($dealership->departmentSales->viber, $data['departmentSales']['viber']);
        $this->assertEquals($dealership->departmentSales->current->name, $data['departmentSales']['translations'][$dealership->departmentSales->current->lang]['name']);
        $this->assertEquals($dealership->departmentSales->current->address, $data['departmentSales']['translations'][$dealership->departmentSales->current->lang]['address']);

        $this->assertEquals($dealership->departmentSales->schedule[3]->day, $data['departmentSales']['schedule'][3]['day']);
        $this->assertEquals($dealership->departmentSales->schedule[3]->from, $data['departmentSales']['schedule'][3]['from']);
        $this->assertEquals($dealership->departmentSales->schedule[3]->to, $data['departmentSales']['schedule'][3]['to']);


        $this->assertEquals($dealership->departmentService->email, $data['departmentService']['email']);
        $this->assertEquals($dealership->departmentService->telegram, $data['departmentService']['telegram']);
        $this->assertEquals($dealership->departmentService->viber, $data['departmentService']['viber']);
        $this->assertEquals($dealership->departmentService->current->name, $data['departmentService']['translations'][$dealership->departmentSales->current->lang]['name']);
        $this->assertEquals($dealership->departmentService->current->address, $data['departmentService']['translations'][$dealership->departmentSales->current->lang]['address']);

        $this->assertEquals($dealership->departmentCash->email, $data['departmentCash']['email']);
        $this->assertEquals($dealership->departmentCash->telegram, $data['departmentCash']['telegram']);
        $this->assertEquals($dealership->departmentCash->viber, $data['departmentCash']['viber']);
        $this->assertEquals($dealership->departmentCash->current->name, $data['departmentCash']['translations'][$dealership->departmentSales->current->lang]['name']);
        $this->assertEquals($dealership->departmentCash->current->address, $data['departmentCash']['translations'][$dealership->departmentSales->current->lang]['address']);

        $this->assertEquals($dealership->departmentBody->email, $data['departmentBody']['email']);
        $this->assertEquals($dealership->departmentBody->telegram, $data['departmentBody']['telegram']);
        $this->assertEquals($dealership->departmentBody->viber, $data['departmentBody']['viber']);
        $this->assertEquals($dealership->departmentBody->current->name, $data['departmentBody']['translations'][$dealership->departmentSales->current->lang]['name']);
        $this->assertEquals($dealership->departmentBody->current->address, $data['departmentBody']['translations'][$dealership->departmentSales->current->lang]['address']);

        \Event::assertDispatched(function (ChangeHashEvent $event){
            return $event->alias == Hash::ALIAS_DEALERSHIP;
        });
    }

    /** @test */
    public function add_time_step_to_exist_dealership()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::DEALERSHIP_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $brand = Brand::where('id',1)->first();
        $data = DealershipCreateTest::data($brand->id, true, true);
        // not have timeStep
        $dealership = Dealership::where('id',2)->first();
        $data['id'] = $dealership->id;

        $this->assertEmpty($dealership->timeStep);

        $this->postGraphQL(['query' => $this->getQueryStrOnlyTimeStep($data)]);

        $dealership->refresh();
        $this->assertNotEmpty($dealership->timeStep);

        unset($data['timeStep']);

        $this->postGraphQL(['query' => $this->getQueryStr($data)]);
        $dealership->refresh();
        $this->assertEmpty($dealership->timeStep);
    }

    /** @test */
    public function not_auth()
    {
        $this->adminBuilder()
            ->createRoleWithPerm(Permissions::DEALERSHIP_EDIT)
            ->create();


        $brand = Brand::where('id',1)->first();
        $data = DealershipCreateTest::data($brand->id);
        $dealership = Dealership::where('id',1)->first();
        $data['id'] = $dealership->id;

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::DEALERSHIP_CREATE)
            ->create();
        $this->loginAsAdmin($admin);

        $brand = Brand::where('id',1)->first();
        $data = DealershipCreateTest::data($brand->id);
        $dealership = Dealership::where('id',1)->first();
        $data['id'] = $dealership->id;

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                dealershipEdit(input:{
                    id: "%s",
                    brandId: "%s",
                    website: "%s",
                    lat: "%s",
                    lon: "%s",
                    translations: [
                        {lang: "%s", name: "%s", text: "%s", address: "%s"}
                        {lang: "%s", name: "%s", text: "%s", address: "%s"}
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
            $data['id'],
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

    private function getQueryStrOnlyTimeStep(array $data): string
    {
        return sprintf('
            mutation {
                dealershipEdit(input:{
                    id: "%s",
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
                    timeStep {
                        id
                        step
                        service {
                            id
                        }
                    }
                    translations {
                        name
                        lang
                    }

                }
            }',
            $data['id'],
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
}


