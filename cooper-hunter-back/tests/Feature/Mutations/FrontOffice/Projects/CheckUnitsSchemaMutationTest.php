<?php

namespace Tests\Feature\Mutations\FrontOffice\Projects;

use App\Enums\Catalog\Products\ProductUnitSubType;
use App\Enums\Catalog\Products\ProductUnitType;
use App\GraphQL\Mutations\FrontOffice\Projects\CheckUnitsSchemaMutation;
use App\Models\Catalog\Products\UnitType;
use App\Models\Projects\Project;
use App\Models\Projects\System;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Catalog\ProductBuilder;
use Tests\Builders\Catalog\ProductSerialNumberBuilder;
use Tests\Builders\Projects\ProjectBuilder;
use Tests\Builders\Projects\SystemBuilder;
use Tests\TestCase;
use Tests\Traits\Models\ProjectCreateTrait;

class CheckUnitsSchemaMutationTest extends TestCase
{
    use DatabaseTransactions;
    use ProjectCreateTrait;

    public const MUTATION = CheckUnitsSchemaMutation::NAME;

    protected ProjectBuilder $projectBuilder;
    protected SystemBuilder $systemBuilder;
    protected ProductBuilder $productBuilder;
    protected ProductSerialNumberBuilder $productSerialNumberBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->projectBuilder = resolve(ProjectBuilder::class);
        $this->systemBuilder = resolve(SystemBuilder::class);
        $this->productBuilder = resolve(ProductBuilder::class);
        $this->productSerialNumberBuilder = resolve(ProductSerialNumberBuilder::class);
    }

    /** @test */
    public function tech_add_success_units_monoblock(): void
    {
        $tech = $this->loginAsTechnicianWithRole();

        /** @var Project $project */
        $project = $this->projectBuilder
            ->setMember($tech)
            ->create();

        /** @var System $system */
        $system = $this->systemBuilder->setProject($project)->create();

        $monoblockUnit = UnitType::query()->where('name', ProductUnitType::MONOBLOCK)->first();

        $product_1 = $this->productBuilder->setUnitTypeId($monoblockUnit->id)->create();
        $productSerialNumber_1 = $this->productSerialNumberBuilder->setProduct($product_1)->create();

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'system' => [
                    'id' => $system->id,
                    'units' => [
                        [
                            'product_id' => $product_1->id,
                            'serial_number' => $productSerialNumber_1->serial_number,
                        ]
                    ]
                ]
            ],
        );

        $this->postGraphQL($query->getMutation())
            ->assertOk()
            ->assertJson([
                'data' => [
                    self::MUTATION => true
                ]
            ])
            ;
    }

    /** @test */
    public function tech_add_success_units_outdoor_single(): void
    {
        $tech = $this->loginAsTechnicianWithRole();

        /** @var Project $project */
        $project = $this->projectBuilder
            ->setMember($tech)
            ->create();

        /** @var System $system */
        $system = $this->systemBuilder->setProject($project)->create();

        $outdoorUnit = UnitType::query()->where('name', ProductUnitType::OUTDOOR)->first();
        $indoorUnit = UnitType::query()->where('name', ProductUnitType::INDOOR)->first();

        $product_1 = $this->productBuilder->setUnitTypeId($outdoorUnit->id)
            ->setSubUnitType(ProductUnitSubType::SINGLE())->create();
        $productSerialNumber_1 = $this->productSerialNumberBuilder->setProduct($product_1)->create();

        $product_2 = $this->productBuilder->setUnitTypeId($indoorUnit->id)->create();
        $productSerialNumber_2 = $this->productSerialNumberBuilder->setProduct($product_2)->create();

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'system' => [
                    'id' => $system->id,
                    'units' => [
                        [
                            'product_id' => $product_1->id,
                            'serial_number' => $productSerialNumber_1->serial_number,
                        ],
                        [
                            'product_id' => $product_2->id,
                            'serial_number' => $productSerialNumber_2->serial_number,
                        ]
                    ]
                ]
            ]
        );

        $this->postGraphQL($query->getMutation())
            ->assertOk()
            ->assertJson([
                'data' => [
                    self::MUTATION => true
                ]
            ])
        ;
    }

    /** @test */
    public function tech_add_success_units_outdoor_multi(): void
    {
        $tech = $this->loginAsTechnicianWithRole();

        /** @var Project $project */
        $project = $this->projectBuilder
            ->setMember($tech)
            ->create();

        /** @var System $system */
        $system = $this->systemBuilder->setProject($project)->create();

        $outdoorUnit = UnitType::query()->where('name', ProductUnitType::OUTDOOR)->first();
        $indoorUnit = UnitType::query()->where('name', ProductUnitType::INDOOR)->first();

        $product_1 = $this->productBuilder->setUnitTypeId($outdoorUnit->id)
            ->setSubUnitType(ProductUnitSubType::MULTI())->create();
        $productSerialNumber_1 = $this->productSerialNumberBuilder->setProduct($product_1)->create();

        $product_2 = $this->productBuilder->setUnitTypeId($indoorUnit->id)->create();
        $productSerialNumber_2 = $this->productSerialNumberBuilder->setProduct($product_2)->create();

        $product_3 = $this->productBuilder->setUnitTypeId($indoorUnit->id)->create();
        $productSerialNumber_3 = $this->productSerialNumberBuilder->setProduct($product_3)->create();

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'system' => [
                    'id' => $system->id,
                    'units' => [
                        [
                            'product_id' => $product_1->id,
                            'serial_number' => $productSerialNumber_1->serial_number,
                        ],
                        [
                            'product_id' => $product_2->id,
                            'serial_number' => $productSerialNumber_2->serial_number,
                        ],
                        [
                            'product_id' => $product_3->id,
                            'serial_number' => $productSerialNumber_3->serial_number,
                        ]
                    ]
                ]
            ],
        );

        $this->postGraphQL($query->getMutation())
            ->assertOk()
            ->assertJson([
                'data' => [
                    self::MUTATION => true
                ]
            ])
        ;
    }

    /** @test */
    public function fail_tech_monoblock_must_be_one(): void
    {
        $tech = $this->loginAsTechnicianWithRole();

        /** @var Project $project */
        $project = $this->projectBuilder
            ->setMember($tech)
            ->create();

        /** @var System $system */
        $system = $this->systemBuilder->setProject($project)->create();

        $monoblockUnit = UnitType::query()->where('name', ProductUnitType::MONOBLOCK)->first();

        $product_1 = $this->productBuilder->setUnitTypeId($monoblockUnit->id)->create();
        $productSerialNumber_1 = $this->productSerialNumberBuilder->setProduct($product_1)->create();

        $product_2 = $this->productBuilder->setUnitTypeId($monoblockUnit->id)->create();
        $productSerialNumber_2 = $this->productSerialNumberBuilder->setProduct($product_2)->create();

        $this->assertTrue($product_1->unitType->isMonoblock());
        $this->assertTrue($product_2->unitType->isMonoblock());

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'system' => [
                    'id' => $system->id,
                    'units' => [
                        [
                            'product_id' => $product_1->id,
                            'serial_number' => $productSerialNumber_1->serial_number,
                        ],
                        [
                            'product_id' => $product_2->id,
                            'serial_number' => $productSerialNumber_2->serial_number,
                        ]
                    ]
                ]
            ]
        );

        $res = $this->postGraphQL($query->getMutation());

        $this->assertTranslatedMessage($res, __('exceptions.commercial.warranty.monoblock_must_be_one'));
    }

    /** @test */
    public function fail_tech_outdoor_must_have_sub_type(): void
    {
        $tech = $this->loginAsTechnicianWithRole();

        /** @var Project $project */
        $project = $this->projectBuilder
            ->setMember($tech)
            ->create();

        /** @var System $system */
        $system = $this->systemBuilder->setProject($project)->create();

        $outdoorUnit = UnitType::query()->where('name', ProductUnitType::OUTDOOR)->first();

        $product_1 = $this->productBuilder->setUnitTypeId($outdoorUnit->id)->create();
        $productSerialNumber_1 = $this->productSerialNumberBuilder->setProduct($product_1)->create();

        $this->assertTrue($product_1->unitType->isOutdoor());

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'system' => [
                    'id' => $system->id,
                    'units' => [
                        [
                            'product_id' => $product_1->id,
                            'serial_number' => $productSerialNumber_1->serial_number,
                        ]
                    ]
                ]
            ]
        );

        $res = $this->postGraphQL($query->getMutation());

        $this->assertTranslatedMessage($res, __('exceptions.commercial.warranty.outdoor_has_no_sub_type'));
    }

    /** @test */
    public function fail_tech_outdoor_single_has_more_indoor(): void
    {
        $tech = $this->loginAsTechnicianWithRole();

        /** @var Project $project */
        $project = $this->projectBuilder
            ->setMember($tech)
            ->create();

        /** @var System $system */
        $system = $this->systemBuilder->setProject($project)->create();

        $outdoorUnit = UnitType::query()->where('name', ProductUnitType::OUTDOOR)->first();
        $indoorUnit = UnitType::query()->where('name', ProductUnitType::INDOOR)->first();

        $product_1 = $this->productBuilder->setSubUnitType(ProductUnitSubType::SINGLE())
            ->setUnitTypeId($outdoorUnit->id)->create();
        $productSerialNumber_1 = $this->productSerialNumberBuilder->setProduct($product_1)->create();

        $product_2 = $this->productBuilder->setUnitTypeId($indoorUnit->id)->create();
        $productSerialNumber_2 = $this->productSerialNumberBuilder->setProduct($product_2)->create();

        $product_3 = $this->productBuilder->setUnitTypeId($indoorUnit->id)->create();
        $productSerialNumber_3 = $this->productSerialNumberBuilder->setProduct($product_3)->create();

        $this->assertTrue($product_1->unitType->isOutdoor());

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'system' => [
                    'id' => $system->id,
                    'units' => [
                        [
                            'product_id' => $product_1->id,
                            'serial_number' => $productSerialNumber_1->serial_number,
                        ],
                        [
                            'product_id' => $product_2->id,
                            'serial_number' => $productSerialNumber_2->serial_number,
                        ],
                        [
                            'product_id' => $product_3->id,
                            'serial_number' => $productSerialNumber_3->serial_number,
                        ]
                    ]
                ]
            ]
        );

        $res = $this->postGraphQL($query->getMutation());

        $this->assertTranslatedMessage($res, __('exceptions.commercial.warranty.outdoor_single_has_more_indoor'));
    }

    /** @test */
    public function fail_tech_outdoor_single_has_one_indoor_not_unit_type(): void
    {
        $tech = $this->loginAsTechnicianWithRole();

        /** @var Project $project */
        $project = $this->projectBuilder
            ->setMember($tech)
            ->create();

        /** @var System $system */
        $system = $this->systemBuilder->setProject($project)->create();

        $outdoorUnit = UnitType::query()->where('name', ProductUnitType::OUTDOOR)->first();
        $indoorUnit = UnitType::query()->where('name', ProductUnitType::INDOOR)->first();

        $product_1 = $this->productBuilder->setSubUnitType(ProductUnitSubType::SINGLE())
            ->setUnitTypeId($outdoorUnit->id)->create();
        $productSerialNumber_1 = $this->productSerialNumberBuilder->setProduct($product_1)->create();

        $product_2 = $this->productBuilder->create();
        $productSerialNumber_2 = $this->productSerialNumberBuilder->setProduct($product_2)->create();

        $this->assertTrue($product_1->unitType->isOutdoor());

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'system' => [
                    'id' => $system->id,
                    'units' => [
                        [
                            'product_id' => $product_1->id,
                            'serial_number' => $productSerialNumber_1->serial_number,
                        ],
                        [
                            'product_id' => $product_2->id,
                            'serial_number' => $productSerialNumber_2->serial_number,
                        ],
                    ]
                ]
            ]
        );

        $res = $this->postGraphQL($query->getMutation());

        $this->assertTranslatedMessage($res, __('exceptions.commercial.warranty.outdoor_single_has_more_indoor'));
    }

    /** @test */
    public function fail_tech_outdoor_single_has_one_indoor_not_indoor(): void
    {
        $tech = $this->loginAsTechnicianWithRole();

        /** @var Project $project */
        $project = $this->projectBuilder
            ->setMember($tech)
            ->create();

        /** @var System $system */
        $system = $this->systemBuilder->setProject($project)->create();

        $outdoorUnit = UnitType::query()->where('name', ProductUnitType::OUTDOOR)->first();
        $accessoryUnit = UnitType::query()->where('name', ProductUnitType::ACCESSORY)->first();

        $product_1 = $this->productBuilder->setSubUnitType(ProductUnitSubType::SINGLE())
            ->setUnitTypeId($outdoorUnit->id)->create();
        $productSerialNumber_1 = $this->productSerialNumberBuilder->setProduct($product_1)->create();

        $product_2 = $this->productBuilder->setUnitTypeId($accessoryUnit->id)->create();
        $productSerialNumber_2 = $this->productSerialNumberBuilder->setProduct($product_2)->create();

        $this->assertTrue($product_1->unitType->isOutdoor());

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'system' => [
                    'id' => $system->id,
                    'units' => [
                        [
                            'product_id' => $product_1->id,
                            'serial_number' => $productSerialNumber_1->serial_number,
                        ],
                        [
                            'product_id' => $product_2->id,
                            'serial_number' => $productSerialNumber_2->serial_number,
                        ],
                    ]
                ]
            ]
        );

        $res = $this->postGraphQL($query->getMutation());

        $this->assertTranslatedMessage($res, __('exceptions.commercial.warranty.outdoor_single_has_more_indoor'));
    }

    /** @test */
    public function fail_tech_outdoor_multi_has_one_indoor(): void
    {
        $tech = $this->loginAsTechnicianWithRole();

        /** @var Project $project */
        $project = $this->projectBuilder
            ->setMember($tech)
            ->create();

        /** @var System $system */
        $system = $this->systemBuilder->setProject($project)->create();

        $outdoorUnit = UnitType::query()->where('name', ProductUnitType::OUTDOOR)->first();
        $indoorUnit = UnitType::query()->where('name', ProductUnitType::INDOOR)->first();

        $product_1 = $this->productBuilder->setSubUnitType(ProductUnitSubType::MULTI())
            ->setUnitTypeId($outdoorUnit->id)->create();
        $productSerialNumber_1 = $this->productSerialNumberBuilder->setProduct($product_1)->create();

        $product_2 = $this->productBuilder->setUnitTypeId($indoorUnit->id)->create();
        $productSerialNumber_2 = $this->productSerialNumberBuilder->setProduct($product_2)->create();

        $this->assertTrue($product_1->unitType->isOutdoor());

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'system' => [
                    'id' => $system->id,
                    'units' => [
                        [
                            'product_id' => $product_1->id,
                            'serial_number' => $productSerialNumber_1->serial_number,
                        ],
                        [
                            'product_id' => $product_2->id,
                            'serial_number' => $productSerialNumber_2->serial_number,
                        ],
                    ]
                ]
            ]
        );

        $res = $this->postGraphQL($query->getMutation());

        $this->assertTranslatedMessage($res, __('exceptions.commercial.warranty.outdoor_multi_consist_indoor'));
    }

    /** @test */
    public function fail_tech_outdoor_multi_has_more_five_indoor(): void
    {
        $tech = $this->loginAsTechnicianWithRole();

        /** @var Project $project */
        $project = $this->projectBuilder
            ->setMember($tech)
            ->create();

        /** @var System $system */
        $system = $this->systemBuilder->setProject($project)->create();

        $outdoorUnit = UnitType::query()->where('name', ProductUnitType::OUTDOOR)->first();
        $indoorUnit = UnitType::query()->where('name', ProductUnitType::INDOOR)->first();

        $product_1 = $this->productBuilder->setSubUnitType(ProductUnitSubType::MULTI())
            ->setUnitTypeId($outdoorUnit->id)->create();
        $productSerialNumber_1 = $this->productSerialNumberBuilder->setProduct($product_1)->create();

        $product_2 = $this->productBuilder->setUnitTypeId($indoorUnit->id)->create();
        $productSerialNumber_2 = $this->productSerialNumberBuilder->setProduct($product_2)->create();

        $product_3 = $this->productBuilder->setUnitTypeId($indoorUnit->id)->create();
        $productSerialNumber_3 = $this->productSerialNumberBuilder->setProduct($product_3)->create();

        $product_4 = $this->productBuilder->setUnitTypeId($indoorUnit->id)->create();
        $productSerialNumber_4 = $this->productSerialNumberBuilder->setProduct($product_4)->create();

        $product_5 = $this->productBuilder->setUnitTypeId($indoorUnit->id)->create();
        $productSerialNumber_5 = $this->productSerialNumberBuilder->setProduct($product_5)->create();

        $product_6 = $this->productBuilder->setUnitTypeId($indoorUnit->id)->create();
        $productSerialNumber_6 = $this->productSerialNumberBuilder->setProduct($product_6)->create();

        $product_7 = $this->productBuilder->setUnitTypeId($indoorUnit->id)->create();
        $productSerialNumber_7 = $this->productSerialNumberBuilder->setProduct($product_7)->create();

        $this->assertTrue($product_1->unitType->isOutdoor());

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'system' => [
                    'id' => $system->id,
                    'units' => [
                        [
                            'product_id' => $product_1->id,
                            'serial_number' => $productSerialNumber_1->serial_number,
                        ],
                        [
                            'product_id' => $product_2->id,
                            'serial_number' => $productSerialNumber_2->serial_number,
                        ],
                        [
                            'product_id' => $product_3->id,
                            'serial_number' => $productSerialNumber_3->serial_number,
                        ],
                        [
                            'product_id' => $product_4->id,
                            'serial_number' => $productSerialNumber_4->serial_number,
                        ],
                        [
                            'product_id' => $product_5->id,
                            'serial_number' => $productSerialNumber_5->serial_number,
                        ],
                        [
                            'product_id' => $product_6->id,
                            'serial_number' => $productSerialNumber_6->serial_number,
                        ],
                        [
                            'product_id' => $product_7->id,
                            'serial_number' => $productSerialNumber_7->serial_number,
                        ],

                    ]
                ]
            ]
        );

        $res = $this->postGraphQL($query->getMutation());

        $this->assertTranslatedMessage($res, __('exceptions.commercial.warranty.outdoor_multi_consist_indoor'));
    }

    /** @test */
    public function fail_tech_outdoor_multi_has_indoors_not_indoor(): void
    {
        $tech = $this->loginAsTechnicianWithRole();

        /** @var Project $project */
        $project = $this->projectBuilder
            ->setMember($tech)
            ->create();

        /** @var System $system */
        $system = $this->systemBuilder->setProject($project)->create();

        $accessoryUnit = UnitType::query()->where('name', ProductUnitType::ACCESSORY)->first();

        $product_1 = $this->productBuilder->setSubUnitType(ProductUnitSubType::MULTI())
            ->setUnitTypeId($accessoryUnit->id)->create();
        $productSerialNumber_1 = $this->productSerialNumberBuilder->setProduct($product_1)->create();

        $this->assertTrue($product_1->unitType->isAccessory());

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'system' => [
                    'id' => $system->id,
                    'units' => [
                        [
                            'product_id' => $product_1->id,
                            'serial_number' => $productSerialNumber_1->serial_number,
                        ],
                    ]
                ]
            ]
        );

        $res = $this->postGraphQL($query->getMutation());

        $this->assertTranslatedMessage($res, __('exceptions.commercial.warranty.must_be_monoblock_or_outdoor'));
    }
}
