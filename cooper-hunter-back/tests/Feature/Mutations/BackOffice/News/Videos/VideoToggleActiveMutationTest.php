<?php

namespace Tests\Feature\Mutations\BackOffice\News\Videos;

use App\GraphQL\Mutations\BackOffice\News\Videos\VideoToggleActiveMutation;
use App\Models\News\Video;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VideoToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = VideoToggleActiveMutation::NAME;

    public function test_toggle_active(): void
    {
        $this->loginAsSuperAdmin();

        $video = Video::factory()->create();

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'id' => $video->id,
            ],
            [
                'id',
                'active',
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJsonPath('data.' . self::MUTATION . '.active', false)
            ->assertJsonStructure(
                [
                    'data' => [
                        self::MUTATION => [
                            'id',
                            'active',
                        ],
                    ],
                ]
            );
    }
}
