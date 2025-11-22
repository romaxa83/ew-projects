<?php

namespace Tests\Feature\Http\Api\OneC\Catalog\Videos;

use App\Models\Catalog\Videos\Group;
use App\Models\Catalog\Videos\GroupTranslation;
use App\Models\Catalog\Videos\VideoLink;
use App\Models\Catalog\Videos\VideoLinkTranslation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VideoLinkControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_get_list(): void
    {
        $this->loginAsModerator();

        VideoLink::factory()
            ->has(VideoLinkTranslation::factory()->enLocale(), 'translation')
            ->for(
                Group::factory()
                    ->has(GroupTranslation::factory()->enLocale(), 'translation')
            )
            ->create();

        $this->getJson(route('1c.video_links'))
            ->assertOk();
    }
}
