<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Categories;

use App\GraphQL\Mutations\BackOffice\Utilities\Media\MediaUploadMutation;
use App\Models\Catalog\Categories\Category;
use App\Models\Media\Media;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Tests\Traits\Storage\TestStorage;

class CategoryImageUploadTest extends TestCase
{
    use DatabaseTransactions;
    use TestStorage;

    public const MUTATION = MediaUploadMutation::NAME;

    public function test_it_can_attach_image(): void
    {
        $this->fakeMediaStorage();

        $this->loginAsSuperAdmin();

        $category = Category::factory()->create();

        $image = UploadedFile::fake()->image('category.jpg');

        $attributes = [
            'operations' => sprintf(
                '{"query": "mutation ($media: Upload!) {%s (model_id: %d, model_type: %s, media: [$media])}"}',
                self::MUTATION,
                $category->id,
                $category::MORPH_NAME,
            ),
            'map' => '{ "media": ["variables.media"] }',
            'media' => $image,
        ];

        $this->postGraphQLBackOfficeUpload($attributes)
            ->assertOk()
            ->assertJsonPath('data.'.self::MUTATION, true);

        $this->assertDatabaseHas(Media::TABLE, ['model_type' => $category::MORPH_NAME, 'model_id' => $category->id]);
    }
}
