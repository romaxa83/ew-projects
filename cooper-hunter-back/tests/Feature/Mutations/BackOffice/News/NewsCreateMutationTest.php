<?php

declare(strict_types=1);

namespace Tests\Feature\Mutations\BackOffice\News;

use App\GraphQL\Mutations\BackOffice\News\NewsCreateMutation;
use App\Models\News\News;
use App\Models\News\NewsTranslation;
use App\Models\News\Tag;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class NewsCreateMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = NewsCreateMutation::NAME;

    public function test_do_success(): void
    {
        $this->loginAsSuperAdmin();

        $this->assertDatabaseCount(News::TABLE, 0);
        $this->assertDatabaseCount(NewsTranslation::TABLE, 0);

        $this->mutation($this->getData())
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::MUTATION => [
                            'id'
                        ],
                    ]
                ]
            );

        $this->assertDatabaseCount(News::TABLE, 1);
        $this->assertDatabaseCount(NewsTranslation::TABLE, 2);
    }

    public function test_not_permitted_user_get_no_permission_error(): void
    {
        $this->loginAsAdmin();

        $this->assertServerError($this->mutation($this->getData()), 'No permission');
    }

    public function test_guest_get_unauthorized_error(): void
    {
        $this->assertServerError($this->mutation($this->getData()), 'Unauthorized');
    }

    protected function mutation(array $args): TestResponse
    {
        $query = new GraphQLQuery(
            static::MUTATION,
            $args,
            [
                'id',
                'tag' => [
                    'color',
                    'translation' => [
                        'title',
                    ],
                ],
            ]
        );

        return $this->postGraphQLBackOffice($query->getMutation());
    }

    protected function getData(): array
    {
        return [
            'news' => [
                'tag_id' => Tag::first()->id,
                'active' => true,
                'slug' => 'slug',
                'translations' => [
                    [
                        'language' => 'en',
                        'title' => 'en title',
                        'description' => 'en description',
                        'short_description' => 'en description',
                        'seo_title' => 'seo title en',
                        'seo_description' => 'seo description en',
                        'seo_h1' => 'seo h1 en',
                    ],
                    [
                        'language' => 'es',
                        'title' => 'es title',
                        'description' => 'es description',
                        'short_description' => 'es description',
                        'seo_title' => 'seo title es',
                        'seo_description' => 'seo description es',
                        'seo_h1' => 'seo h1 es',
                    ],
                ],
            ]
        ];
    }
}
