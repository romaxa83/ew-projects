<?php

declare(strict_types=1);

namespace Tests\Feature\Mutations\BackOffice\News\Videos;

use App\GraphQL\Mutations\BackOffice\News\Videos\VideoCreateMutation;
use App\Models\News\Video;
use App\Models\News\VideoTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class VideosCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = VideoCreateMutation::NAME;

    public function test_do_success(): void
    {
        $this->loginAsSuperAdmin();

        $this->assertDatabaseCount(Video::TABLE, 0);
        $this->assertDatabaseCount(VideoTranslation::TABLE, 0);

        $translateForCheck = [
            'language' => 'en',
            'seo_title' => 'custom seo title en',
            'seo_description' => 'custom seo description en',
            'seo_h1' => 'custom seo h1 en'
        ];

        $this->assertDatabaseMissing(VideoTranslation::TABLE, $translateForCheck);

        $this->mutation($this->getArgs(), $this->getSelect())
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::MUTATION => $this->getSelect()
                    ]
                ]
            );

        $this->assertDatabaseCount(Video::TABLE, 1);
        $this->assertDatabaseCount(VideoTranslation::TABLE, 2);
        $this->assertDatabaseHas(VideoTranslation::TABLE, $translateForCheck);
    }

    protected function mutation(array $args, array $select): TestResponse
    {
        $query = GraphQLQuery::mutation(static::MUTATION)
            ->args($args)
            ->select($select)
            ->make();

        return $this->postGraphQLBackOffice($query);
    }

    protected function getArgs(): array
    {
        return [
            'video' => [
                'active' => true,
                'slug' => 'slug',
                'translations' => [
                    [
                        'language' => 'en',
                        'video_link' => $this->faker->imageUrl,
                        'title' => 'en title',
                        'description' => 'en description',
                        'seo_title' => 'custom seo title en',
                        'seo_description' => 'custom seo description en',
                        'seo_h1' => 'custom seo h1 en',
                    ],
                    [
                        'language' => 'es',
                        'video_link' => $this->faker->imageUrl,
                        'title' => 'es title',
                        'description' => 'es description',
                        'seo_title' => 'custom seo title es',
                        'seo_description' => 'custom seo description es',
                        'seo_h1' => 'custom seo h1 es',
                    ],
                ],
            ]
        ];
    }

    protected function getSelect(): array
    {
        return [
            'id',
            'active',
            'translation' => [
                'language',
                'video_link',
                'title',
                'description',
                'seo_title',
                'seo_description',
                'seo_h1',
            ],
        ];
    }

    public function test_not_permitted_user_get_no_permission_error(): void
    {
        $this->loginAsAdmin();

        $this->assertServerError($this->mutation($this->getArgs(), $this->getSelect()), 'No permission');
    }

    public function test_guest_get_unauthorized_error(): void
    {
        $this->assertServerError($this->mutation($this->getArgs(), $this->getSelect()), 'Unauthorized');
    }
}
