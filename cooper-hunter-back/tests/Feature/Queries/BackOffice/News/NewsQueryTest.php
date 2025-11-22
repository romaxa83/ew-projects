<?php

declare(strict_types=1);

namespace Tests\Feature\Queries\BackOffice\News;

use App\GraphQL\Queries\BackOffice\News\NewsQuery;
use App\Models\News\News;
use App\Models\News\NewsTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Tests\TestCase;
use Tests\Traits\Storage\TestStorage;

class NewsQueryTest extends TestCase
{
    use DatabaseTransactions;
    use TestStorage;

    public const QUERY = NewsQuery::NAME;

    /**
     * @throws FileNotFoundException
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function test_get_list_success(): void
    {
        $this->fakeMediaStorage();

        $this->loginAsSuperAdmin();

        $news = News::factory()
            ->times(3)
            ->has(NewsTranslation::factory()->allLocales(), 'translations')
            ->create()
            ->first();

        $news->addMedia(
            $this->getSampleImage(),
        )->toMediaCollection($news::MEDIA_COLLECTION_NAME);

        $this->query()
            ->assertOk()
            ->assertJsonCount(3, 'data.' . self::QUERY . '.data')
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            'data' => [
                                [
                                    'id',
                                ]
                            ],
                        ],
                    ],
                ]
            );
    }

    protected function query(array $args = []): TestResponse
    {
        $query = new GraphQLQuery(
            self::QUERY,
            $args,
            [
                'data' => [
                    'id',
                    'image' => [
                        'id'
                    ],
                ]
            ]
        );

        return $this->postGraphQLBackOffice($query->getQuery());
    }

    public function test_not_permitted_user_get_no_permission_error(): void
    {
        $this->loginAsAdmin();

        $this->assertServerError($this->query(), 'No permission');
    }

    public function test_guest_get_unauthorized_error(): void
    {
        $this->assertServerError($this->query(), 'Unauthorized');
    }
}
