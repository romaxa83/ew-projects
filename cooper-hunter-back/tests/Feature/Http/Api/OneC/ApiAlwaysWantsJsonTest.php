<?php

namespace Tests\Feature\Http\Api\OneC;

use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Categories\CategoryTranslation;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ApiAlwaysWantsJsonTest extends TestCase
{
    public function test_api_wants_json(): void
    {
        $this->loginAsModerator();

        $this->get(route('1c.categories.show', 0))
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(
                [
                    'message' => 'This request only allows JSON content.'
                ]
            );
    }

    public function test_api_wants_json_exists_entity(): void
    {
        $this->loginAsModerator();

        $category = Category::factory()
            ->has(
                CategoryTranslation::factory()->allLocales(),
                'translations'
            )
            ->create();

        $this->get(route('1c.categories.show', $category->id))
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(
                [
                    'message' => 'This request only allows JSON content.'
                ]
            );
    }
}
