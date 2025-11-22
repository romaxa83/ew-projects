<?php

namespace Api\BodyShop\Tags;

use App\Models\Tags\Tag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TagUpdateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_not_update_for_unauthorized_users()
    {
        $tag = Tag::factory()->create(['carrier_id' => null]);

        $this->putJson(route('body-shop.tags.update', $tag))->assertUnauthorized();
    }

    public function test_it_update_by_bs_super_admin()
    {
        $tag = Tag::factory()->create(['carrier_id' => null]);

        $formRequest = [
            'name' => 'Name Test',
            'color' => '#ffffff',
            'type' => Tag::TYPE_VEHICLE_OWNER,
        ];

        $this->assertDatabaseMissing(Tag::TABLE_NAME, $formRequest);

        $this->loginAsBodyShopSuperAdmin();

        $this->putJson(route('body-shop.tags.update', $tag), $formRequest)
            ->assertOk();

        $this->assertDatabaseHas(Tag::TABLE_NAME, $formRequest);
    }
}
