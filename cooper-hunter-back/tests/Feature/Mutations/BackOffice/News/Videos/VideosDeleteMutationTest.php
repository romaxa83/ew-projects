<?php

declare(strict_types=1);

namespace Tests\Feature\Mutations\BackOffice\News\Videos;

use App\GraphQL\Mutations\BackOffice\News\Videos\VideoDeleteMutation;
use App\Models\News\Video;
use App\Models\News\VideoTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class VideosDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = VideoDeleteMutation::NAME;

    public function test_do_success(): void
    {
        $this->loginAsSuperAdmin();

        $video = Video::factory()
            ->has(VideoTranslation::factory()->allLocales(), 'translations')
            ->create();

        $this->assertDatabaseCount(VideoTranslation::TABLE, 2);

        $this->mutation(['id' => $video->id])
            ->assertOk();

        $this->assertModelMissing($video);
        $this->assertDatabaseCount(VideoTranslation::TABLE, 0);
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

    public function test_not_permitted_user_get_no_permission_error(): void
    {
        $this->loginAsAdmin();
        $video = Video::factory()->create();

        $this->assertServerError($this->mutation(['id' => $video->id]), 'No permission');
    }

    public function test_guest_get_unauthorized_error(): void
    {
        $video = Video::factory()->create();

        $this->assertServerError($this->mutation(['id' => $video->id]), 'Unauthorized');
    }
}
