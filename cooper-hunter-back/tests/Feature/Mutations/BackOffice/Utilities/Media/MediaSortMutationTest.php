<?php

namespace Tests\Feature\Mutations\BackOffice\Utilities\Media;

use App\GraphQL\Mutations\BackOffice\Utilities\Media\MediaSortMutation;
use App\Models\Catalog\Products\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Tests\Builders\Catalog\ProductBuilder;
use Tests\TestCase;

class MediaSortMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = MediaSortMutation::NAME;

    protected ProductBuilder $productBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->productBuilder = resolve(ProductBuilder::class);
    }

    /** @test */
    public function success_sort_product(): void
    {
        $this->loginAsSuperAdmin();

        $product_1 = $this->productBuilder->create();

        $media_1 = UploadedFile::fake()->image('product1.jpg');
        $media_2 = UploadedFile::fake()->image('product2.jpg');
        $media_3 = UploadedFile::fake()->image('product2.jpg');
        $media_4 = UploadedFile::fake()->image('product2.jpg');

        $product_1->addMedia($media_1)->toMediaCollection(Product::MEDIA_COLLECTION_NAME);
        $product_1->addMedia($media_2)->toMediaCollection(Product::MEDIA_COLLECTION_NAME);
        $product_1->addMedia($media_3)->toMediaCollection(Product::MEDIA_COLLECTION_NAME);
        $product_1->addMedia($media_4)->toMediaCollection(Product::MEDIA_COLLECTION_NAME);

        $this->assertEquals(0, $product_1->media[0]->sort);
        $this->assertEquals(0, $product_1->media[1]->sort);
        $this->assertEquals(0, $product_1->media[2]->sort);
        $this->assertEquals(0, $product_1->media[3]->sort);

        $data = [
            'media_ids' => [
                $product_1->media[2]->id,
                $product_1->media[0]->id,
                $product_1->media[3]->id,
                $product_1->media[1]->id,
            ],
        ];

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => true,
                ]
            ])
        ;

        $product_1->refresh();

        $this->assertEquals(0, $product_1->media[0]->sort);
        $this->assertEquals($product_1->media[0]->id, data_get($data, 'media_ids.0'));

        $this->assertEquals(1, $product_1->media[1]->sort);
        $this->assertEquals($product_1->media[1]->id, data_get($data, 'media_ids.1'));

        $this->assertEquals(2, $product_1->media[2]->sort);
        $this->assertEquals($product_1->media[2]->id, data_get($data, 'media_ids.2'));

        $this->assertEquals(3, $product_1->media[3]->sort);
        $this->assertEquals($product_1->media[3]->id, data_get($data, 'media_ids.3'));
    }

    protected function getQueryStr(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    media_ids: [%s, %s, %s, %s],
                )
            }',
            self::MUTATION,
            data_get($data, 'media_ids.0'),
            data_get($data, 'media_ids.1'),
            data_get($data, 'media_ids.2'),
            data_get($data, 'media_ids.3'),
        );
    }
}


