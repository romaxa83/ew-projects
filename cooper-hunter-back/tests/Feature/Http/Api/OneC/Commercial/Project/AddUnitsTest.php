<?php

namespace Tests\Feature\Http\Api\OneC\Commercial\Project;

use App\Models\Commercial\CommercialProject;
use App\Services\Commercial\CommercialProjectUnitService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery\MockInterface;
use Tests\Builders\Catalog\ProductBuilder;
use Tests\Builders\Commercial\ProjectBuilder;
use Tests\Builders\Commercial\ProjectUnitBuilder;
use Tests\TestCase;

class AddUnitsTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected $projectBuilder;
    protected $projectUnitBuilder;
    protected $productBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->projectBuilder = resolve(ProjectBuilder::class);
        $this->projectUnitBuilder = resolve(ProjectUnitBuilder::class);
        $this->productBuilder = resolve(ProductBuilder::class);
    }

    /** @test */
    public function success_add(): void
    {
        $this->loginAsModerator();

        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setData([
            'guid' => $this->faker->uuid
        ])->create();

        $this->assertEmpty($project->units);

        $product_1 = $this->productBuilder->create();
        $product_2 = $this->productBuilder->create();

        $data = [
            'data' => [
                [
                    'product_guid' => $product_1->guid,
                    'serial_numbers' => [
                        '0001', '0002'
                    ]
                ],
                [
                    'product_guid' => $product_2->guid,
                    'serial_numbers' => [
                        '0004', '0005', '0006'
                    ]
                ]
            ]
        ];

        $this->postJson(route('1c.commercial-project.add-units', ['guid' => $project->guid]), $data)
            ->assertOk()
            ->assertJson([
                'data' => 'Done',
                'success' => true
            ]);

        $project->refresh();

        $this->assertCount(5, $project->units);
        $this->assertEquals($project->units[0]->serial_number, data_get($data, 'data.0.serial_numbers.0'));
        $this->assertEquals($project->units[0]->product->id, $product_1->id);
        $this->assertEquals($project->units[1]->serial_number, data_get($data, 'data.0.serial_numbers.1'));
        $this->assertEquals($project->units[1]->product->id, $product_1->id);
        $this->assertEquals($project->units[2]->serial_number, data_get($data, 'data.1.serial_numbers.0'));
        $this->assertEquals($project->units[2]->product->id, $product_2->id);
        $this->assertEquals($project->units[3]->serial_number, data_get($data, 'data.1.serial_numbers.1'));
        $this->assertEquals($project->units[3]->product->id, $product_2->id);
        $this->assertEquals($project->units[4]->serial_number, data_get($data, 'data.1.serial_numbers.2'));
        $this->assertEquals($project->units[4]->product->id, $product_2->id);
    }

    /** @test */
    public function success_add_new(): void
    {
        $this->loginAsModerator();

        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setData([
            'guid' => $this->faker->uuid
        ])->create();

        $product_1 = $this->productBuilder->create();

        $this->projectUnitBuilder->setProject($project)->setProduct($product_1)->create();
        $this->projectUnitBuilder->setProject($project)->setProduct($product_1)->create();

        $project->refresh();

        $this->assertCount(2, $project->units);

        $data = [
            'data' => [
                [
                    'product_guid' => $product_1->guid,
                    'serial_numbers' => [
                        '0001'
                    ]
                ],
            ]
        ];

        $this->postJson(route('1c.commercial-project.add-units', ['guid' => $project->guid]), $data)
            ->assertOk()
            ->assertJson([
                'data' => 'Done',
                'success' => true
            ]);

        $project->refresh();

        $this->assertCount(3, $project->units);
    }

//    /** @test */
//    public function success_update(): void
//    {
//        $this->loginAsModerator();
//
//        /** @var $project CommercialProject */
//        $project = $this->projectBuilder->create();
//
//        $data = $this->data();
//
//        $unit_1 = $this->projectUnitBuilder->setProject($project)
//            ->setSerialNumber(data_get($data, 'data.0.serial_number'))->create();
//        $unit_2 = $this->projectUnitBuilder->setProject($project)
//            ->setSerialNumber(data_get($data, 'data.1.serial_number'))->create();
//
//        $this->assertCount(2, $project->units);
//        $this->assertNotEquals($unit_1->name, data_get($data, 'data.0.name'));
//        $this->assertNotEquals($unit_2->name, data_get($data, 'data.1.name'));
//
//        $this->postJson(route('1c.commercial-project.add-units', ['id' => $project->id]), $data)
//            ->assertOk()
//            ->assertJson([
//                'data' => 'Done',
//                'success' => true
//            ]);
//
//        $project->refresh();
//        $unit_1->refresh();
//        $unit_2->refresh();
//
//        $this->assertCount(2, $project->units);
//        $this->assertEquals($unit_1->name, data_get($data, 'data.0.name'));
//        $this->assertEquals($unit_2->name, data_get($data, 'data.1.name'));
//    }

    /** @test */
    public function fail_not_project(): void
    {
        $this->loginAsModerator();

        $product_1 = $this->productBuilder->create();
        $data = [
            'data' => [
                [
                    'product_guid' => $product_1->guid,
                    'serial_numbers' => [
                        '0001', '0002'
                    ]
                ],
            ]
        ];

        $this->postJson(route('1c.commercial-project.add-units', ['guid' => 'wewe']), $data)
            ->assertStatus(404)
            ->assertJson([
                'data' => 'Model not found',
                'success' => false
            ]);
    }

    /** @test */
    public function fail_something_wrong_to_service(): void
    {
        $this->loginAsModerator();

        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setData([
            'guid' => $this->faker->uuid
        ])->create();

        $product_1 = $this->productBuilder->create();
        $data = [
            'data' => [
                [
                    'product_guid' => $product_1->guid,
                    'serial_numbers' => [
                        '0001', '0002'
                    ]
                ],
            ]
        ];

        $this->mock(CommercialProjectUnitService::class, function(MockInterface $mock){
            $mock->shouldReceive("createOrUpdate")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->postJson(route('1c.commercial-project.add-units', ['guid' => $project->guid]), $data)
            ->assertStatus(500)
            ->assertJson([
                'data' => "some exception message",
                'success' => false
            ]);
    }
}
