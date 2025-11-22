<?php

declare(strict_types=1);

namespace Tests\Feature\Mutations\BackOffice\News;

use App\GraphQL\Mutations\BackOffice\News\NewsDeleteMutation;
use App\Models\News\News;
use App\Models\News\NewsTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class NewsDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = NewsDeleteMutation::NAME;

    public function test_do_success(): void
    {
        $this->loginAsSuperAdmin();

        $news = News::factory()
            ->has(NewsTranslation::factory()->allLocales(), 'translations')
            ->create();

        $this->assertDatabaseCount(NewsTranslation::TABLE, 2);

        $this->mutation(['id' => $news->id])
            ->assertOk();

        $this->assertModelMissing($news);
        $this->assertDatabaseCount(NewsTranslation::TABLE, 0);
    }

    public function test_not_permitted_user_get_no_permission_error(): void
    {
        $this->loginAsAdmin();
        $news = News::factory()->create();

        $this->assertServerError($this->mutation(['id' => $news->id]), 'No permission');
    }

    public function test_guest_get_unauthorized_error(): void
    {
        $news = News::factory()->create();

        $this->assertServerError($this->mutation(['id' => $news->id]), 'Unauthorized');
    }

    protected function mutation(array $args): TestResponse
    {
        $query = new GraphQLQuery(
            self::MUTATION,
            $args,
            [
                'message'
            ]
        );

        return $this->postGraphQLBackOffice($query->getMutation());
    }
}
