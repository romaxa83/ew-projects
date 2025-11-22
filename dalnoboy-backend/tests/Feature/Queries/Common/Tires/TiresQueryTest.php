<?php

namespace Tests\Feature\Queries\Common\Tires;

use App\GraphQL\Queries\Common\Tires\BaseTiresQuery;
use App\Models\Dictionaries\TireMake;
use App\Models\Dictionaries\TireModel;
use App\Models\Dictionaries\TireRelationshipType;
use App\Models\Dictionaries\TireSize;
use App\Models\Dictionaries\TireSpecification;
use App\Models\Dictionaries\TireType;
use App\Models\Tires\Tire;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TiresQueryTest extends TestCase
{
    use DatabaseTransactions;

    private iterable $tires;

    public function setUp(): void
    {
        parent::setUp();

        $this->tires = Tire::factory()
            ->count(15)
            ->create();
    }

    public function test_get_tires_list_by_admin(): void
    {
        $this->loginAsAdminWithRole();

        $this->check();
    }

    private function check(bool $backoffice = true): void
    {
        $this->{'postGraphQl' . ($backoffice ? 'BackOffice' : '')}(
            GraphQLQuery::query(BaseTiresQuery::NAME)
                ->select(
                    [
                        'data' => [
                            'id',
                            'active',
                            'serial_number',
                            'specification' => [
                                'id',
                            ],
                            'relationship_type' => [
                                'id',
                            ],
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        BaseTiresQuery::NAME => [
                            'data' => [
                                '*' => [
                                    'id',
                                    'active',
                                    'serial_number',
                                    'specification' => [
                                        'id',
                                    ],
                                    'relationship_type' => [
                                        'id',
                                    ],
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(
                Tire::query()
                    ->count(),
                'data.' . BaseTiresQuery::NAME . '.data'
            );
    }

    public function test_get_tires_list_by_user(): void
    {
        $this->loginAsUserWithRole();

        $this->check(false);
    }

    public function test_filter_by_make_model_type(): void
    {
        $this->loginAsAdminWithRole();
        $tireMake = TireMake::factory()->create();
        $tireModel = TireModel::factory()->create();
        $tireType = TireType::factory()->create();
        $specification = TireSpecification::factory()
            ->for($tireMake, 'tireMake')
            ->for($tireModel, 'tireModel')
            ->for($tireType, 'tireType')
            ->create();
        $this->tires[1]->specification_id = $specification->getKey();
        $this->tires[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseTiresQuery::NAME)
                ->args(
                    [
                        'tire_make' => $tireMake->getKey(),
                        'tire_model' => $tireModel->getKey(),
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseTiresQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->tires[1]->id,
                                ],
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . BaseTiresQuery::NAME . '.data');
    }

    public function test_filter_by_relationship_type_size_type(): void
    {
        $this->loginAsAdminWithRole();
        $tireRelationshipType = TireRelationshipType::factory()->create();
        $tireSize = TireSize::factory()
            ->create();
        $tireSpecification = TireSpecification::factory()
            ->for($tireSize, 'tireSize')
            ->create();
        $this->tires[1]->specification_id = $tireSpecification->getKey();
        $this->tires[1]->relationship_type_id = $tireRelationshipType->getKey();
        $this->tires[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseTiresQuery::NAME)
                ->args(
                    [
                        'tire_relationship_type' => $tireRelationshipType->getKey(),
                        'tire_size' => $tireSize->getKey(),
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseTiresQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->tires[1]->id,
                                ],
                            ],
                        ]
                    ]
                ]
            )
            ->assertJsonCount(1, 'data.' . BaseTiresQuery::NAME . '.data');
    }

    public function test_filter_by_active(): void
    {
        $this->loginAsAdminWithRole();
        $this->tires[0]->active = false;
        $this->tires[0]->save();
        $this->tires[1]->active = false;
        $this->tires[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseTiresQuery::NAME)
                ->args(
                    [
                        'active' => false,
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseTiresQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->tires[1]->id,
                                ],
                                [
                                    'id' => $this->tires[0]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseTiresQuery::NAME . '.data');
    }

    public function test_filter_by_moderated(): void
    {
        $this->loginAsAdminWithRole();
        $this->tires[0]->is_moderated = false;
        $this->tires[0]->save();
        $this->tires[1]->is_moderated = false;
        $this->tires[1]->save();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BaseTiresQuery::NAME)
                ->args(
                    [
                        'is_moderated' => false,
                    ]
                )
                ->select(
                    [
                        'data' => [
                            'id',
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseTiresQuery::NAME => [
                            'data' => [
                                [
                                    'id' => $this->tires[1]->id,
                                ],
                                [
                                    'id' => $this->tires[0]->id,
                                ],
                            ]
                        ]
                    ]
                ]
            )
            ->assertJsonCount(2, 'data.' . BaseTiresQuery::NAME . '.data');
    }

    public function test_only_active_records_for_user(): void
    {
        $this->loginAsUserWithRole();
        $this->tires[0]->active = false;
        $this->tires[0]->save();
        $this->tires[1]->active = false;
        $this->tires[1]->save();

        $this->postGraphQL(
            GraphQLQuery::query(BaseTiresQuery::NAME)
                ->select(
                    [
                        'data' => [
                            'id',
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonCount(13, 'data.' . BaseTiresQuery::NAME . '.data');
    }
}
