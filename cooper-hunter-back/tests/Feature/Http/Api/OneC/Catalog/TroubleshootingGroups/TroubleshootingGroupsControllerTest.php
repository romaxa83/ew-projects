<?php

namespace Tests\Feature\Http\Api\OneC\Catalog\TroubleshootingGroups;

use App\Models\Catalog\Troubleshoots\Group;
use App\Models\Catalog\Troubleshoots\GroupTranslation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TroubleshootingGroupsControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_get_list(): void
    {
        $this->loginAsModerator();

        Group::factory()
            ->has(GroupTranslation::factory()->enLocale(), 'translation')
            ->create();

        $this->getJson(route('1c.troubleshooting_groups'))
            ->assertOk();
    }
}
