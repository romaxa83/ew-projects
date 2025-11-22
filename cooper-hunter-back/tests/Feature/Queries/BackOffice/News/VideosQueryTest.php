<?php

declare(strict_types=1);

namespace Tests\Feature\Queries\BackOffice\News;

use App\GraphQL\Queries\BackOffice\News\VideosQuery;
use App\Models\News\Video;
use App\Models\News\VideoTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class VideosQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = VideosQuery::NAME;

    public function test_get_list_success(): void
    {
        $this->loginAsSuperAdmin();

        Video::factory()
            ->times(20)
            ->has(
                VideoTranslation::factory()->allLocales(),
                'translations'
            )
            ->create();

        $this->query($this->getArgs(), $this->getSelect())
            ->assertOk()
            ->assertJsonStructure($this->getJsonStructure());
    }

    protected function query(array $args, array $select): TestResponse
    {
        $query = GraphQLQuery::query(self::QUERY)
            ->args($args)
            ->select($select);

        return $this->postGraphQLBackOffice($query->make());
    }

    protected function getArgs(): array
    {
        return [];
    }

    protected function getSelect(): array
    {
        return [
            'data' => [
                'id',
                'active',
                'translation' => [
                    'video_link',
                    'title',
                    'description',
                ],
            ],
        ];
    }

    protected function getJsonStructure(): array
    {
        return [
            'data' => [
                self::QUERY => [
                    'data' => [
                        [
                            'id',
                            'active',
                            'translation' => [
                                'video_link',
                                'title',
                                'description',
                            ],
                        ]
                    ],
                ],
            ],
        ];
    }

    public function test_not_permitted_user_get_no_permission_error(): void
    {
        $this->loginAsAdmin();

        $this->assertServerError($this->query($this->getArgs(), $this->getSelect()), 'No permission');
    }

    public function test_guest_get_unauthorized_error(): void
    {
        $this->assertServerError($this->query($this->getArgs(), $this->getSelect()), 'Unauthorized');
    }
}
