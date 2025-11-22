<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Products;

use App\GraphQL\Mutations\BackOffice\Utilities\Media\MediaUploadMutation;
use App\Models\Catalog\Products\Product;
use App\Models\Media\Media;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Tests\Traits\Storage\TestStorage;

class ProductImagesUploadTest extends TestCase
{
    use DatabaseTransactions;
    use TestStorage;

    public const MUTATION = MediaUploadMutation::NAME;

    public function test_it_can_attach_image(): void
    {
        $this->fakeMediaStorage();

        $this->loginAsSuperAdmin();

        $product = Product::factory()->create();

        $image1 = UploadedFile::fake()->image('product1.jpg');
        $image2 = UploadedFile::fake()->image('product2.jpg');
        $image3 = UploadedFile::fake()->image('product3.jpg');
        $image4 = UploadedFile::fake()->image('product4.jpg');

        $attributes = [
            'operations' => sprintf(
                '{"query": "mutation ($media: [Upload!]!) {%s (model_id: %d, model_type: %s, media: $media)}"}',
                self::MUTATION,
                $product->id,
                $product::MORPH_NAME,
            ),
            'map' => '{ "media": ["variables.media"] }',
            'media' => [$image1, $image2, $image3, $image4],
        ];

        $this->postGraphQLBackOfficeUpload($attributes)
            ->assertOk()
            ->assertJsonPath('data.'.self::MUTATION, true);

        $this->assertDatabaseCount(Media::TABLE, 4);
        $this->assertDatabaseHas(Media::TABLE, ['model_type' => $product::MORPH_NAME, 'model_id' => $product->id]);
    }
}
