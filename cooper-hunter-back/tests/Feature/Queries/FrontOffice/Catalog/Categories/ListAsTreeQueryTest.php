<?php

namespace Tests\Feature\Queries\FrontOffice\Catalog\Categories;

use App\GraphQL\Queries\FrontOffice\Catalog\Categories\CategoriesAsTreeQuery;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Categories\CategoryTranslation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ListAsTreeQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = CategoriesAsTreeQuery::NAME;

    public function test_list_as_tree(): void
    {
        Category::factory()
            ->times(5)
            ->has(CategoryTranslation::factory()->allLocales(), 'translations')
            ->create();

        $this->loginAsTechnicianWithRole();

        $query = sprintf(
            'query {
                %s
                {
                    id
                    active
                    children {
                        id
                        active
                        translation {
                            title
                        }
                        children {
                            id
                            active
                            translation {
                                title
                            }
                            children {
                                id
                                active
                                translation {
                                    title
                                }
                                children {
                                    id
                                    active
                                    translation {
                                        title
                                    }
                                    children {
                                        id
                                        active
                                        translation {
                                            title
                                        }
                                        children {
                                            id
                                            active
                                            translation {
                                                title
                                            }
                                            children {
                                                id
                                                active
                                                translation {
                                                    title
                                                }
                                                children {
                                                    id
                                                    active
                                                    translation {
                                                        title
                                                    }
                                                    children {
                                                        id
                                                        active
                                                        translation {
                                                            title
                                                        }
                                                        children {
                                                            id
                                                            active
                                                            translation {
                                                                title
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    translation {
                        title
                    }
                }
            }',
            self::QUERY,
        );

        $this->postGraphQL(compact('query'))
            ->assertOk()
            ->assertJsonstructure(
                [
                    'data' => [
                        self::QUERY => [
                            [
                                'id',
                                'active',
                                'children'
                            ]
                        ],
                    ],
                ]
            );
    }
}
