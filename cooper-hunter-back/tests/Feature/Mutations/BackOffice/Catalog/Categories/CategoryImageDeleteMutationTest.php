<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Categories;

use App\GraphQL\Mutations\BackOffice\Utilities\Media\MediaDeleteMutation;
use App\Models\Catalog\Categories\Category;
use App\Models\Media\Media;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Tests\TestCase;
use Tests\Traits\Storage\TestStorage;

class CategoryImageDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use TestStorage;

    public const MUTATION = MediaDeleteMutation::NAME;

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function test_can_delete_image(): void
    {
        $this->fakeMediaStorage();
        $this->loginAsSuperAdmin();

        $category = Category::factory()->create();
        $category->addMedia(
            UploadedFile::fake()->image('category1.png')
        )
            ->toMediaCollection(Category::MEDIA_COLLECTION_NAME);

        $query = sprintf(
            'mutation {
                %s (
                    media_id: "%s"
                    model_type: %s
                )
            }',
            self::MUTATION,
            $mediaId = $category->media->first()->id,
            $category::MORPH_NAME,
        );

        $this->assertDatabaseHas(Media::TABLE, ['id' => $mediaId]);

        $this->postGraphQLBackOffice(compact('query'))
            ->assertOk();

        $this->assertDatabaseMissing(Media::TABLE, ['id' => $mediaId]);
    }
}
