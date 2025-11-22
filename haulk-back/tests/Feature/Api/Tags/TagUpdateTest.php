<?php

namespace Api\Tags;

use App\Models\Tags\Tag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TagUpdateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_not_update_for_unauthorized_users()
    {
        $tag = Tag::factory()->create();

        $this->putJson(route('tags.update', $tag))->assertUnauthorized();
    }

    public function test_it_update_by_super_admin()
    {
        $tag = Tag::factory()->create();

        $formRequest = [
            'name' => 'Name Test',
            'color' => '#ffffff',
            'type' => Tag::TYPE_ORDER,
        ];

        $this->assertDatabaseMissing(Tag::TABLE_NAME, $formRequest);

        $this->loginAsCarrierSuperAdmin();

        $this->putJson(route('tags.update', $tag), $formRequest)
            ->assertOk();

        $this->assertDatabaseHas(Tag::TABLE_NAME, $formRequest);
    }
}
