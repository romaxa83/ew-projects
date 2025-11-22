<?php

namespace Api\Tags;

use App\Models\Tags\Tag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TagShowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_not_show_for_unauthorized_users()
    {
        $tag = Tag::factory()->create();

        $this->getJson(route('tags.show', $tag))->assertUnauthorized();
    }

    public function test_it_not_show_for_not_permitted_users()
    {
        $tag = Tag::factory()->create();

        $this->loginAsCarrierDispatcher();

        $this->getJson(route('tags.show', $tag))
            ->assertForbidden();
    }

    public function test_it_show_for_permitted_users()
    {
        $tag = Tag::factory()->create();

        $this->loginAsCarrierSuperAdmin();

        $this->getJson(route('tags.show', $tag))
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'id',
                'name',
                'color',
                'type',
                'hasRelatedEntities',
            ]]);

        $this->loginAsCarrierAdmin();

        $this->getJson(route('tags.show', $tag))
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'id',
                'name',
                'color',
                'type',
                'hasRelatedEntities',
            ]]);
    }
}
