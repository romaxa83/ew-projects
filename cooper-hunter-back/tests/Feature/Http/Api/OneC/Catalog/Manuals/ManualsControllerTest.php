<?php

namespace Tests\Feature\Http\Api\OneC\Catalog\Manuals;

use App\Models\Catalog\Manuals\Manual;
use App\Models\Catalog\Manuals\ManualGroup;
use App\Models\Catalog\Manuals\ManualGroupTranslation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ManualsControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_get_list(): void
    {
        $this->loginAsModerator();

        Manual::factory()
            ->for(
                ManualGroup::factory()
                    ->has(ManualGroupTranslation::factory()->enLocale(), 'translation'),
                'group'
            )
            ->create();

        $this->getJson(route('1c.manuals'))
            ->assertOk();
    }
}
