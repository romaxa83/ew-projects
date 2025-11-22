<?php

namespace Tests\Feature\Queries\FrontOffice\Catalog\Products;

use App\Contracts\Members\Member;
use App\Enums\Catalog\Videos\VideoLinkTypeEnum;
use App\GraphQL\Queries\FrontOffice\Catalog\Products\ProductQuery;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Categories\CategoryTranslation;
use App\Models\Catalog\Favourites\Favourite;
use App\Models\Catalog\Features\Specification;
use App\Models\Catalog\Manuals\Manual;
use App\Models\Catalog\Manuals\ManualGroup;
use App\Models\Catalog\Manuals\ManualGroupTranslation;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Videos\Group;
use App\Models\Catalog\Videos\GroupTranslation;
use App\Models\Catalog\Videos\VideoLink;
use App\Models\Catalog\Videos\VideoLinkTranslation;
use App\Models\Technicians\Technician;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Catalog\LabelBuilder;
use Tests\Builders\Catalog\Manuals\ManualBuilder;
use Tests\Builders\Catalog\Manuals\ManualGroupBuilder;
use Tests\Builders\Catalog\ProductBuilder;
use Tests\Builders\Catalog\Video\LinkBuilder;
use Tests\TestCase;

class ProductQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = ProductQuery::NAME;

    protected ProductBuilder $productBuilder;
    protected LinkBuilder $videoLinkBuilder;
    protected ManualGroupBuilder $manualGroupBuilder;
    protected ManualBuilder $manualBuilder;
    protected LabelBuilder $labelBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->productBuilder = resolve(ProductBuilder::class);
        $this->videoLinkBuilder = resolve(LinkBuilder::class);
        $this->manualGroupBuilder = resolve(ManualGroupBuilder::class);
        $this->manualBuilder = resolve(ManualBuilder::class);
        $this->labelBuilder = resolve(LabelBuilder::class);
    }

    public function test_user_can_view_product(): void
    {
        $this->assertCanViewProduct(
            $this->loginAsUserWithRole()
        );
    }

    protected function assertCanViewProduct(Member $member): void
    {
        $product = Product::factory()
            ->for(
                $category = Category::factory()
                    ->has(CategoryTranslation::factory()->allLocales(), 'translations')
                    ->create()
            )
            ->hasAttached(
                VideoLink::factory()
                    ->has(
                        VideoLinkTranslation::factory()
                            ->allLocales(),
                        'translations'
                    )->for(
                        Group::factory()
                            ->has(
                                GroupTranslation::factory()->allLocales(),
                                'translations'
                            )
                    )
            )
            ->hasAttached(
                Manual::factory()
                    ->for(
                        ManualGroup::factory()
                            ->has(
                                ManualGroupTranslation::factory()->allLocales(),
                                'translations'
                            ),
                        'group'
                    )
            )
            ->create();

        Favourite::factory()
            ->forMember($member)
            ->forProduct($product)
            ->create();

        $query = sprintf(
            'query {
                %s (
                    id: %s
                ) {
                    id
                    is_favourite
                    breadcrumbs {
                        id,
                        title
                    }
                    brand {
                        id
                    }
                    video_groups {
                        id
                        translation {
                            title
                        }
                        links {
                            id
                            link
                            translation {
                                title
                            }
                        }
                    }
                    manual_groups {
                        id
                        translation {
                            title
                        }
                        manuals {
                            id
                            pdf {
                                url
                            }
                        }
                    }
                    labels {
                        id
                    }
                }
            }
            ',
            self::QUERY,
            $product->id
        );

        $this->postGraphQL(compact('query'))
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'id' => $product->id,
                            'is_favourite' => true,
                            'brand' => [
                                'id' => $product->brand->id
                            ],
                            'breadcrumbs' => [
                                [
                                    'id' => $category->id,
                                    'title' => $category->translation->title,
                                ]
                            ],
                            'labels' => []
                        ],
                    ],
                ]
            )
            ->assertJsonCount(0, 'data.'.self::QUERY.'.labels' )
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            'id',
                            'is_favourite',
                            'video_groups' => [
                                [
                                    'id',
                                    'translation' => [
                                        'title',
                                    ],
                                    'links' => [
                                        [
                                            'id',
                                            'link',
                                            'translation' => [
                                                'title',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'manual_groups' => [
                                [
                                    'id',
                                    'translation' => [
                                        'title',
                                    ],
                                    'manuals' => [
                                        [
                                            'id',
                                            'pdf' => [
                                                'url'
                                            ],
                                        ],
                                    ],
                                ],
                            ],

                        ],
                    ],
                ],
            );
    }

    public function test_technician_can_view_product(): void
    {
        $this->assertCanViewProduct(
            $this->loginAsTechnicianWithRole()
        );
    }

    public function test_favourite_displays_for_current_user(): void
    {
        $member = $this->loginAsUserWithRole();

        $product = Product::factory()->create();

        Favourite::factory()
            ->forMember($member)
            ->forProduct($product)
            ->create();

        $query = new GraphQLQuery(
            self::QUERY,
            [
                'id' => $product->id,
            ],
            [
                'is_favourite'
            ],
        );

        $this->postGraphQL($query->getQuery())
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'is_favourite' => true,
                        ],
                    ]
                ]
            );
    }

    public function test_favourite_not_displays_for_different_user(): void
    {
        $this->loginAsUserWithRole();

        $product = Product::factory()->create();

        $member = Technician::factory()->create();

        Favourite::factory()
            ->forMember($member)
            ->forProduct($product)
            ->create();

        $query = new GraphQLQuery(
            self::QUERY,
            [
                'id' => $product->id,
            ],
            [
                'is_favourite'
            ],
        );

        $this->postGraphQL($query->getQuery())
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'is_favourite' => false,
                        ],
                    ]
                ]
            );
    }

    public function test_manual_group_duplication_issue_fix(): void
    {
        $manuals1 = Manual::factory()
            ->times(3)
            ->for(ManualGroup::factory(), 'group')
            ->create();

        $manuals2 = Manual::factory()
            ->times(3)
            ->for(ManualGroup::factory(), 'group')
            ->create();

        $product = Product::factory()
            ->hasAttached($manuals1)
            ->hasAttached($manuals2)
            ->create();

        $query = GraphQLQuery::query(self::QUERY)
            ->args(
                [
                    'id' => $product->id
                ]
            )
            ->select(
                [
                    'id',
                    'manual_groups' => [
                        'manuals' => [
                            'id'
                        ]
                    ],
                ]
            )
            ->make();

        $this->postGraphQL($query)
            ->assertJsonCount(2, 'data.' . self::QUERY . '.manual_groups');
    }

    public function test_get_specifications(): void
    {
        $p = Product::factory()
            ->has(Specification::factory())
            ->create();

        $query = GraphQLQuery::query(self::QUERY)
            ->args(
                [
                    'id' => $p->id
                ]
            )
            ->select(
                [
                    'specifications' => [
                        'id'
                    ]
                ]
            )
            ->make();

        $this->postGraphQL($query)
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            'specifications' => [
                                [
                                    'id'
                                ]
                            ],
                        ],
                    ],
                ]
            );
    }

    /** @test */
    public function success_see_video_manual_by_tech(): void
    {
        $this->loginAsTechnicianWithRole();

        $link_1 = $this->videoLinkBuilder->setType(VideoLinkTypeEnum::COMMERCIAL)->create();
        $link_2 = $this->videoLinkBuilder->setType(VideoLinkTypeEnum::COMMON)->create();
        $link_3 = $this->videoLinkBuilder->setType(VideoLinkTypeEnum::COMMON)->create();

        $product = $this->productBuilder->setVideoLinks($link_1, $link_2, $link_3)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr($product->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'id' => $product->id
                    ],
                ]
            ])
            ->assertJsonCount(3, 'data.'.self::QUERY.'.video_links')
        ;
    }

    /** @test */
    public function success_not_see_video_manual_by_tech(): void
    {
        $this->loginAsTechnicianWithRole(
            Technician::factory()->certified()->verified()
                ->create(['is_commercial_certification' => false])
        );

        $link_1 = $this->videoLinkBuilder->setType(VideoLinkTypeEnum::COMMERCIAL)->create();
        $link_2 = $this->videoLinkBuilder->setType(VideoLinkTypeEnum::COMMON)->create();
        $link_3 = $this->videoLinkBuilder->setType(VideoLinkTypeEnum::COMMON)->create();

        $product = $this->productBuilder->setVideoLinks($link_1, $link_2, $link_3)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr($product->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'id' => $product->id
                    ],
                ]
            ])
            ->assertJsonCount(2, 'data.'.self::QUERY.'.video_links')
        ;
    }

    /** @test */
    public function success_not_see_video_manual_by_user(): void
    {
        $link_1 = $this->videoLinkBuilder->setType(VideoLinkTypeEnum::COMMERCIAL)->create();
        $link_2 = $this->videoLinkBuilder->setType(VideoLinkTypeEnum::COMMON)->create();
        $link_3 = $this->videoLinkBuilder->setType(VideoLinkTypeEnum::COMMON)->create();

        $product = $this->productBuilder->setVideoLinks($link_1, $link_2, $link_3)->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr($product->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'id' => $product->id
                    ],
                ]
            ])
            ->assertJsonCount(2, 'data.'.self::QUERY.'.video_links')
        ;
    }

    protected function getQueryStr($id): string
    {
        return sprintf(
            '
            {
                %s (id: %s) {
                    id
                    video_links {
                        id
                    }
                    manual_groups {
                        id
                    }
                    labels {
                        id
                    }
                }
            }',
            self::QUERY,
            $id
        );
    }

    /** @test */
    public function success_see_manual_groups_by_certified_tech(): void
    {
        $this->loginAsTechnicianWithRole(
            Technician::factory()->certified()->verified()
                ->create(['is_commercial_certification' => true])
        );

        $group_1 = $this->manualGroupBuilder->setShowCommercialCertified(true)->create();
        $group_2 = $this->manualGroupBuilder->setShowCommercialCertified(false)->create();

        $manual_1 = $this->manualBuilder->setGroup($group_1)->create();
        $manual_2 = $this->manualBuilder->setGroup($group_1)->create();
        $manual_3 = $this->manualBuilder->setGroup($group_2)->create();
        $manual_4 = $this->manualBuilder->setGroup($group_2)->create();

        $product = $this->productBuilder->setManuals(
            $manual_1, $manual_2, $manual_3, $manual_4
        )->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr($product->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'id' => $product->id,
                        'manual_groups' => [
                            ['id' => $group_1->id],
                            ['id' => $group_2->id],
                        ]
                    ],
                ]
            ])
            ->assertJsonCount(2, 'data.'.self::QUERY.'.manual_groups')
        ;
    }

    /** @test */
    public function success_see_manual_groups_by_not_certified_tech(): void
    {
        $this->loginAsTechnicianWithRole(
            Technician::factory()->certified()->verified()
                ->create(['is_commercial_certification' => false])
        );

        $group_1 = $this->manualGroupBuilder->setShowCommercialCertified(true)->create();
        $group_2 = $this->manualGroupBuilder->setShowCommercialCertified(false)->create();

        $manual_1 = $this->manualBuilder->setGroup($group_1)->create();
        $manual_2 = $this->manualBuilder->setGroup($group_1)->create();
        $manual_3 = $this->manualBuilder->setGroup($group_2)->create();
        $manual_4 = $this->manualBuilder->setGroup($group_2)->create();

        $product = $this->productBuilder->setManuals(
            $manual_1, $manual_2, $manual_3, $manual_4
        )->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr($product->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'id' => $product->id,
                        'manual_groups' => [
                            ['id' => $group_2->id],
                        ]
                    ],
                ]
            ])
            ->assertJsonCount(1, 'data.'.self::QUERY.'.manual_groups')
        ;
    }

    /** @test */
    public function success_see_manual_groups_by_user(): void
    {
        $this->loginAsTechnicianWithRole(
            Technician::factory()->certified()->verified()
                ->create(['is_commercial_certification' => false])
        );

        $group_1 = $this->manualGroupBuilder->setShowCommercialCertified(true)->create();
        $group_2 = $this->manualGroupBuilder->setShowCommercialCertified(false)->create();

        $manual_1 = $this->manualBuilder->setGroup($group_1)->create();
        $manual_2 = $this->manualBuilder->setGroup($group_1)->create();
        $manual_3 = $this->manualBuilder->setGroup($group_2)->create();
        $manual_4 = $this->manualBuilder->setGroup($group_2)->create();

        $product = $this->productBuilder->setManuals(
            $manual_1, $manual_2, $manual_3, $manual_4
        )->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr($product->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'id' => $product->id,
                        'manual_groups' => [
                            ['id' => $group_2->id],
                        ]
                    ],
                ]
            ])
            ->assertJsonCount(1, 'data.'.self::QUERY.'.manual_groups')
        ;
    }

    /** @test */
    public function see_labels(): void
    {
        $this->loginAsTechnicianWithRole(
            Technician::factory()->certified()->verified()
                ->create(['is_commercial_certification' => false])
        );

        $label_1 = $this->labelBuilder->create();
        $label_2 = $this->labelBuilder->create();
        $label_3 = $this->labelBuilder->create();

        $product = $this->productBuilder->setLabels(
            $label_1, $label_2, $label_3
        )->create();

        $this->postGraphQL([
            'query' => $this->getQueryStr($product->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'id' => $product->id,
                        'labels' => [
                            ['id' => $label_3->id],
                            ['id' => $label_2->id],
                            ['id' => $label_1->id],
                        ]
                    ],
                ]
            ])
            ->assertJsonCount(3, 'data.'.self::QUERY.'.labels')
        ;
    }
}
