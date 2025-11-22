<?php

declare(strict_types=1);

namespace Tests\Feature\Mutations\BackOffice\News\Videos;

use App\GraphQL\Mutations\BackOffice\News\Videos\VideoUpdateMutation;
use App\Models\News\Video;
use App\Models\News\VideoTranslation;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class VideosUpdateMutationTest extends VideosCreateMutationTest
{
    use DatabaseTransactions;

    public const MUTATION = VideoUpdateMutation::NAME;

    public function test_do_success(): void
    {
        $this->loginAsSuperAdmin();

        $video = Video::factory()
            ->has(VideoTranslation::factory()->allLocales(), 'translations')
            ->create();

        $data = $this->getArgs();
        $newTranslation = [
            'language' => 'en',
            'seo_title' => 'custom seo title en',
            'seo_description' => 'custom seo description en',
            'seo_h1' => 'custom seo h1 en'
        ];

        $data['video']['id'] = $video->id;
        $data['video']['translations'][0] = array_merge(
            $data['video']['translations'][0],
            $newTranslation
        );

        $this->assertDatabaseMissing(VideoTranslation::TABLE, $newTranslation);

        $this->mutation($data, $this->getSelect())
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::MUTATION => $this->getSelect(),
                    ]
                ]
            );

        $this->assertDatabaseHas(VideoTranslation::TABLE, $newTranslation);
    }

    public function test_not_permitted_user_get_no_permission_error(): void
    {
        $this->loginAsAdmin();

        $news = Video::factory()
            ->has(VideoTranslation::factory()->allLocales(), 'translations')
            ->create();

        $data = $this->getArgs();
        $data['video']['id'] = $news->id;

        $this->assertServerError($this->mutation($data, $this->getSelect()), 'No permission');
    }

    public function test_guest_get_unauthorized_error(): void
    {
        $news = Video::factory()
            ->has(VideoTranslation::factory()->allLocales(), 'translations')
            ->create();

        $data = $this->getArgs();
        $data['video']['id'] = $news->id;

        $this->assertServerError($this->mutation($data, $this->getSelect()), 'Unauthorized');
    }
}
