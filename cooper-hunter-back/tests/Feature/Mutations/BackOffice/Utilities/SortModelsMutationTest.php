<?php

namespace Tests\Feature\Mutations\BackOffice\Utilities;

use App\Enums\Commercial\Commissioning\AnswerStatus;
use App\Enums\Commercial\Commissioning\ProtocolStatus;
use App\Enums\Commercial\Commissioning\ProtocolType;
use App\Enums\Commercial\Commissioning\QuestionStatus;
use App\Enums\Menu\MenuBlockEnum;
use App\Enums\Sorting\SortingModelsEnum;
use App\GraphQL\Mutations\BackOffice\Utilities\Sorting\SortModelsMutation;
use App\GraphQL\Queries\BackOffice\Commercial\CommercialProjectsQuery;
use App\GraphQL\Queries\BackOffice\Menu\MenuQuery;
use App\GraphQL\Queries\Common\Catalog\Products\BaseProductsQuery;
use App\Models\Catalog\Products\Product;
use App\Models\Commercial\CommercialProject;
use App\Models\Menu\Menu;
use App\Permissions\Catalog\Products\ListPermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Commercial\Commissioning\ProjectProtocolBuilder;
use Tests\Builders\Commercial\Commissioning\ProjectProtocolQuestionBuilder;
use Tests\Builders\Commercial\Commissioning\ProtocolBuilder;
use Tests\Builders\Commercial\Commissioning\QuestionBuilder;
use Tests\Builders\Commercial\ProjectBuilder;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class SortModelsMutationTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;

    protected ProtocolBuilder $protocolBuilder;
    protected QuestionBuilder $questionBuilder;
    protected ProjectBuilder $projectBuilder;
    protected ProjectProtocolBuilder $protocolProjectBuilder;
    protected ProjectProtocolQuestionBuilder $protocolProjectQuestionBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->questionBuilder = resolve(QuestionBuilder::class);
        $this->protocolBuilder = resolve(ProtocolBuilder::class);
        $this->projectBuilder = resolve(ProjectBuilder::class);
        $this->protocolProjectBuilder = resolve(ProjectProtocolBuilder::class);
        $this->protocolProjectQuestionBuilder = resolve(ProjectProtocolQuestionBuilder::class);
    }

    public function test_sort_models(): void
    {
        $this->loginByAdminManager([ListPermission::KEY]);

        Product::factory()
            ->times(50)
            ->create();

        $productsPageQuery = GraphQLQuery::query(BaseProductsQuery::NAME)
            ->args(
                [
                    'page' => 2,
                    'sort' => 'sort-desc'
                ]
            )
            ->select(
                [
                    'data' => [
                        'id'
                    ],
                ]
            )
            ->make();

        $ids = $this->postGraphQLBackOffice($productsPageQuery)
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        BaseProductsQuery::NAME => [
                            'data' => [
                                '*' => [
                                    'id'
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->json('data.' . BaseProductsQuery::NAME . '.data');

        $ids = array_column($ids, 'id');

        shuffle($ids);

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(SortModelsMutation::NAME)
                ->args(
                    [
                        'model' => SortingModelsEnum::PRODUCT()
                            ->toScalar(true),
                        'data' => $ids
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SortModelsMutation::NAME => true
                    ]
                ]
            );

        $this->postGraphQLBackOffice($productsPageQuery)
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseProductsQuery::NAME => [
                            'data' => array_map(
                                fn(string $id) => ['id' => $id],
                                $ids
                            )
                        ]
                    ]
                ]
            );
    }

    public function test_sort_menu(): void
    {
        $this->loginAsAdminManager();

        $menus = Menu::factory()
            ->count(5)
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(SortModelsMutation::NAME)
                ->args(
                    [
                        'model' => SortingModelsEnum::MENU()
                            ->toScalar(true),
                        'data' => [
                            $menus[2]->id,
                            $menus[4]->id,
                            $menus[0]->id,
                            $menus[1]->id,
                        ]
                    ]
                )
                ->make()
        );

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(MenuQuery::NAME)
                ->args(
                    [
                        'block' => MenuBlockEnum::OTHER()
                    ]
                )
                ->select(
                    [
                        'id'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        MenuQuery::NAME => [
                            [
                                'id' => $menus[2]->id,
                            ],
                            [
                                'id' => $menus[3]->id,
                            ],
                            [
                                'id' => $menus[4]->id,
                            ],
                            [
                                'id' => $menus[0]->id,
                            ],
                            [
                                'id' => $menus[1]->id,
                            ],
                        ]
                    ]
                ]
            );
    }

    /** @test */
    public function sort_protocol(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $project CommercialProject */
        $project = $this->projectBuilder->create();

        $protocol_1 = $this->protocolBuilder->setSort(3)->setType(ProtocolType::PRE_COMMISSIONING)->create();
        $protocol_2 = $this->protocolBuilder->setSort(2)->setType(ProtocolType::COMMISSIONING)->create();
        $protocol_3 = $this->protocolBuilder->setSort(1)->setType(ProtocolType::COMMISSIONING)->create();

        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::PENDING)->setProject($project)->create();
        $projectProtocol_2 = $this->protocolProjectBuilder->setProtocol($protocol_2)
            ->setStatus(ProtocolStatus::PENDING)->setProject($project)->create();
        $projectProtocol_3 = $this->protocolProjectBuilder->setProtocol($protocol_3)
            ->setStatus(ProtocolStatus::PENDING)->setProject($project)->create();

        $this->assertEquals($protocol_1->sort, $projectProtocol_1->sort);
        $this->assertEquals($protocol_2->sort, $projectProtocol_2->sort);
        $this->assertEquals($protocol_3->sort, $projectProtocol_3->sort);

        $this->assertEquals($project->projectProtocols[0]->id, $projectProtocol_3->id);
        $this->assertEquals($project->projectProtocols[1]->id, $projectProtocol_2->id);
        $this->assertEquals($project->projectProtocols[2]->id, $projectProtocol_1->id);

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(SortModelsMutation::NAME)
                ->args(
                    [
                        'model' => SortingModelsEnum::PROTOCOL()
                            ->toScalar(true),
                        'data' => [
                            $protocol_2->id,
                            $protocol_1->id,
                            $protocol_3->id,
                        ]
                    ]
                )
                ->make()
        );

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(CommercialProjectsQuery::NAME)
                ->args(['id' => $project->id])
                ->select([
                    'data' => [
                        'id',
                        'project_protocols' => [
                            'id',
                        ]
                    ],
                ])
                ->make()
        )
            ->assertOk()
            ->assertJson([
                'data' => [
                    CommercialProjectsQuery::NAME => [
                        'data' => [
                            [
                                'id' => $project->id,
                                'project_protocols' => [
                                    ['id' => $projectProtocol_2->id],
                                    ['id' => $projectProtocol_1->id],
                                    ['id' => $projectProtocol_3->id]
                                ]
                            ]
                        ]
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function sort_protocol_question(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $project CommercialProject */
        $project = $this->projectBuilder->create();

        $protocol_1 = $this->protocolBuilder->setSort(3)->setType(ProtocolType::PRE_COMMISSIONING)->create();
        $projectProtocol_1 = $this->protocolProjectBuilder->setProtocol($protocol_1)
            ->setStatus(ProtocolStatus::PENDING)->setProject($project)->create();

        $q_1 = $this->questionBuilder->setSort(1)->setProtocol($protocol_1)->setStatus(QuestionStatus::ACTIVE())->create();
        $q_2 = $this->questionBuilder->setSort(2)->setProtocol($protocol_1)->setStatus(QuestionStatus::ACTIVE())->create();
        $q_3 = $this->questionBuilder->setSort(3)->setProtocol($protocol_1)->setStatus(QuestionStatus::ACTIVE())->create();
        $q_4 = $this->questionBuilder->setSort(4)->setProtocol($protocol_1)->setStatus(QuestionStatus::ACTIVE())->create();

        $projectProtocolQuestion_1 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setAnswerStatus(AnswerStatus::DRAFT)->setQuestion($q_1)->create();
        $projectProtocolQuestion_2 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setAnswerStatus(AnswerStatus::DRAFT)->setQuestion($q_2)->create();
        $projectProtocolQuestion_3 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setAnswerStatus(AnswerStatus::DRAFT)->setQuestion($q_3)->create();
        $projectProtocolQuestion_4 = $this->protocolProjectQuestionBuilder->setProjectProtocol($projectProtocol_1)
            ->setAnswerStatus(AnswerStatus::DRAFT)->setQuestion($q_4)->create();

        $this->assertEquals($q_1->sort, $projectProtocolQuestion_1->sort);
        $this->assertEquals($q_2->sort, $projectProtocolQuestion_2->sort);
        $this->assertEquals($q_3->sort, $projectProtocolQuestion_3->sort);
        $this->assertEquals($q_4->sort, $projectProtocolQuestion_4->sort);

        $this->assertEquals($project->projectProtocols[0]->projectQuestions[0]->id, $projectProtocolQuestion_4->id);
        $this->assertEquals($project->projectProtocols[0]->projectQuestions[1]->id, $projectProtocolQuestion_3->id);
        $this->assertEquals($project->projectProtocols[0]->projectQuestions[2]->id, $projectProtocolQuestion_2->id);
        $this->assertEquals($project->projectProtocols[0]->projectQuestions[3]->id, $projectProtocolQuestion_1->id);

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(SortModelsMutation::NAME)
                ->args(
                    [
                        'model' => SortingModelsEnum::QUESTION()
                            ->toScalar(true),
                        'data' => [
                            $q_2->id,
                            $q_4->id,
                            $q_1->id,
                            $q_3->id,
                        ]
                    ]
                )
                ->make()
        );

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(CommercialProjectsQuery::NAME)
                ->args(['id' => $project->id])
                ->select([
                    'data' => [
                        'id',
                        'project_protocols' => [
                            'project_questions' => [
                                'id'
                            ],
                        ]
                    ],
                ])
                ->make()
        )
            ->assertOk()
            ->assertJson([
                'data' => [
                    CommercialProjectsQuery::NAME => [
                        'data' => [
                            [
                                'id' => $project->id,
                                'project_protocols' => [
                                    [
                                        'project_questions' => [
                                            ['id' => $projectProtocolQuestion_2->id],
                                            ['id' => $projectProtocolQuestion_4->id],
                                            ['id' => $projectProtocolQuestion_1->id],
                                            ['id' => $projectProtocolQuestion_3->id],
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ])
        ;
    }
}
