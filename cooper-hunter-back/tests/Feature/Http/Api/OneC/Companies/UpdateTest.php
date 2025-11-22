<?php

namespace Tests\Feature\Http\Api\OneC\Companies;

use App\Events\Companies\UpdateCompanyByOnecEvent;
use App\Listeners\Companies\SendCodeForDealerListener;
use App\Models\Companies\Company;
use App\Models\Companies\Corporation;
use App\Services\Companies\CompanyService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;
use Tests\Builders\Company\CompanyBuilder;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected CompanyBuilder $companyBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyBuilder = resolve(CompanyBuilder::class);
    }

    /** @test */
    public function success_update(): void
    {
        Event::fake();

        $this->loginAsModerator();

        /** @var $model Company */
        $model = $this->companyBuilder->setData([
            'guid' => $this->faker->uuid
        ])->create();

        $this->assertTrue($model->status->isDraft());
        $this->assertNull($model->terms);
        $this->assertNull($model->corporation);
        $this->assertNull($model->manager);

        $data = [
            'authorization_code' => $this->faker->postcode,
            'terms' => [
                [
                    'name' => $this->faker->word,
                    'guid' => $this->faker->uuid,
                ],
                [
                    'name' => $this->faker->word,
                    'guid' => $this->faker->uuid,
                ]
            ],
            'corporation' => [
                'guid' => $this->faker->uuid,
                'name' => $this->faker->company,
            ],
            'manager' => [
                'name' => $this->faker->name,
                'email' => $this->faker->safeEmail,
                'phone' => '8989898989',
            ],
            'commercial_manager' => [
                'name' => $this->faker->name,
                'email' => $this->faker->safeEmail,
                'phone' => '8989898989',
            ]
        ];

        $this->postJson(route('1c.companies.update', ['guid' => $model->guid]), $data)
            ->assertOk()
            ->assertJson([
                'data' => 'Done',
                'success' => true
            ]);

        $model->refresh();

        $this->assertEquals($model->terms, data_get($data, 'terms'));
        $this->assertEquals($model->corporation->guid, data_get($data, 'corporation.guid'));
        $this->assertEquals($model->corporation->name, data_get($data, 'corporation.name'));

        $this->assertEquals($model->manager->name, data_get($data, 'manager.name'));
        $this->assertEquals($model->manager->email, data_get($data, 'manager.email'));
        $this->assertEquals($model->manager->phone, data_get($data, 'manager.phone'));

        $this->assertEquals($model->commercialManager->name, data_get($data, 'commercial_manager.name'));
        $this->assertEquals($model->commercialManager->email, data_get($data, 'commercial_manager.email'));
        $this->assertEquals($model->commercialManager->phone, data_get($data, 'commercial_manager.phone'));

        Event::assertDispatched(UpdateCompanyByOnecEvent::class);
    }

    /** @test */
    public function update_only_terms(): void
    {
        $this->loginAsModerator();

        $code = $this->faker->uuid;
        /** @var $model Company */
        $model = $this->companyBuilder->setData([
            'guid' => $this->faker->uuid,
        ])->create();

        $this->assertTrue($model->status->isDraft());
        $this->assertNull($model->terms);
        $this->assertNull($model->corporation);

        $data = [
            'terms' => [
                [
                    'name' => $this->faker->word,
                    'guid' => $this->faker->uuid,
                ],
                [
                    'name' => $this->faker->word,
                    'guid' => $this->faker->uuid,
                ],
            ],
            'corporation' => []
        ];

        $this->postJson(route('1c.companies.update', ['guid' => $model->guid]), $data)
            ->assertOk()
            ->assertJson([
                'data' => 'Done',
                'success' => true
            ]);

        $model->refresh();

        $this->assertTrue($model->status->isDraft());
        $this->assertEquals($model->terms, data_get($data, 'terms'));
        $this->assertEquals($model->term_names, data_get($data, 'terms.0.name') .', '. data_get($data, 'terms.1.name'));
        $this->assertNull($model->corporation);
    }

    /** @test */
    public function update_corporation(): void
    {
        Event::fake([UpdateCompanyByOnecEvent::class]);

        $this->loginAsModerator();

        /** @var $model Company */
        $model = $this->companyBuilder->setData([
            'guid' => $this->faker->uuid
        ])->create();

        $this->assertNull($model->corporation);

        $data = [
            'corporation' => [
                'guid' => $this->faker->uuid,
                'name' => $this->faker->company,
            ]
        ];

        $this->postJson(route('1c.companies.update', ['guid' => $model->guid]), $data)
            ->assertOk()
            ->assertJson([
                'data' => 'Done',
                'success' => true
            ]);

        $model->refresh();

        $this->assertEquals($model->corporation->guid, data_get($data, 'corporation.guid'));
        $this->assertEquals($model->corporation->name, data_get($data, 'corporation.name'));
        $this->assertNull($model->term_names);

        Event::assertNotDispatched(UpdateCompanyByOnecEvent::class);
    }

    /** @test */
    public function update_corporation_if_exist(): void
    {
        $guid = $this->faker->uuid;
        $corp = Corporation::factory()->create([
            'guid' => $guid
        ]);

        $this->loginAsModerator();

        /** @var $model Company */
        $model = $this->companyBuilder->setData([
            'guid' => $this->faker->uuid
        ])->create();

        $this->assertNull($model->corporation);

        $data = [
            'corporation' => [
                'guid' => $guid,
                'name' => $this->faker->company,
            ]
        ];

        $this->postJson(route('1c.companies.update', ['guid' => $model->guid]), $data)
            ->assertOk()
            ->assertJson([
                'data' => 'Done',
                'success' => true
            ]);

        $model->refresh();

        $this->assertEquals($model->corporation->guid, data_get($data, 'corporation.guid'));
    }

    /** @test */
    public function update_manager(): void
    {
        Event::fake([UpdateCompanyByOnecEvent::class]);

        $this->loginAsModerator();

        /** @var $model Company */
        $model = $this->companyBuilder->setData([
            'guid' => $this->faker->uuid
        ])->withManager()->create();

        $data = [
            'manager' => [
                'name' => $this->faker->name,
                'email' => $this->faker->safeEmail,
                'phone' => '8989898989',
            ]
        ];

        $this->assertNotEquals($model->manager->name, data_get($data, 'manager.name'));
        $this->assertNotEquals($model->manager->email->getValue(), data_get($data, 'manager.email'));
        $this->assertNotEquals($model->manager->phone->getValue(), data_get($data, 'manager.phone'));

        $this->postJson(route('1c.companies.update', ['guid' => $model->guid]), $data)
            ->assertOk()
            ->assertJson([
                'data' => 'Done',
                'success' => true
            ]);

        $model->refresh();

        $this->assertEquals($model->manager->name, data_get($data, 'manager.name'));
        $this->assertEquals($model->manager->email, data_get($data, 'manager.email'));
        $this->assertEquals($model->manager->phone, data_get($data, 'manager.phone'));

        Event::assertNotDispatched(UpdateCompanyByOnecEvent::class);
    }

    /** @test */
    public function update_commercial_manager(): void
    {
        Event::fake([UpdateCompanyByOnecEvent::class]);

        $this->loginAsModerator();

        /** @var $model Company */
        $model = $this->companyBuilder->setData([
            'guid' => $this->faker->uuid
        ])->withCommercialManager()->create();

        $data = [
            'commercial_manager' => [
                'name' => $this->faker->name,
                'email' => $this->faker->safeEmail,
                'phone' => '8989898989',
            ]
        ];

        $this->assertNotEquals($model->commercialManager->name, data_get($data, 'commercial_manager.name'));
        $this->assertNotEquals($model->commercialManager->email->getValue(), data_get($data, 'commercial_manager.email'));
        $this->assertNotEquals($model->commercialManager->phone->getValue(), data_get($data, 'commercial_manager.phone'));

        $this->postJson(route('1c.companies.update', ['guid' => $model->guid]), $data)
            ->assertOk()
            ->assertJson([
                'data' => 'Done',
                'success' => true
            ]);

        $model->refresh();

        $this->assertEquals($model->commercialManager->name, data_get($data, 'commercial_manager.name'));
        $this->assertEquals($model->commercialManager->email, data_get($data, 'commercial_manager.email'));
        $this->assertEquals($model->commercialManager->phone, data_get($data, 'commercial_manager.phone'));

        Event::assertNotDispatched(UpdateCompanyByOnecEvent::class);
    }


    /** @test */
    public function fail_not_dealer(): void
    {
        $this->loginAsModerator();

        $guid = '342342423';

        $this->postJson(route('1c.companies.update', ['guid' => $guid]), [])
            ->assertStatus(404)
            ->assertJson([
                'data' => __('exceptions.company.not found by guid' , ['guid' => $guid]),
                'success' => false
            ]);
    }

    /** @test */
    public function fail_something_wrong_to_service(): void
    {
        $this->loginAsModerator();

        /** @var $model Company */
        $model = $this->companyBuilder->setData([
            'guid' => $this->faker->uuid,
        ])->create();

        $this->mock(CompanyService::class, function(MockInterface $mock){
            $mock->shouldReceive("updateOnec")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->postJson(route('1c.companies.update', ['guid' => $model->guid]), [])
            ->assertStatus(500)
            ->assertJson([
                'data' => "some exception message",
                'success' => false
            ]);
    }
}
