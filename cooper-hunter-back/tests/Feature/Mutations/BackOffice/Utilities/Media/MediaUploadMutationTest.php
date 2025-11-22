<?php

namespace Tests\Feature\Mutations\BackOffice\Utilities\Media;

use App\GraphQL\Mutations\BackOffice\Utilities\Media\MediaUploadMutation;
use App\Models\Catalog\Products\Product;
use App\Models\Media\Media;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Tests\Builders\Catalog\ProductBuilder;
use Tests\TestCase;

class MediaUploadMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = MediaUploadMutation::NAME;

    protected ProductBuilder $productBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->productBuilder = resolve(ProductBuilder::class);
    }

    /** @test */
    public function success_upload_media_for_product(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Product */
        $model = $this->productBuilder->create();

        $media_1 = UploadedFile::fake()->image('test_1.jpg');
        $media_2 = UploadedFile::fake()->image('test_2.jpg');
        $media_3 = UploadedFile::fake()->image('test_3.jpg');

        $this->assertEmpty($model->media);

        $attributes = [
            'operations' => sprintf(
                '{"query": "mutation ($media: [Upload!]!) {%s (model_id: %s, model_type: %s, media: $media) }"}',
                self::MUTATION,
                $model->id,
                Product::MORPH_NAME,
            ),
            'map' => '{ "media": ["variables.media"] }',
            'media' => [$media_1, $media_2, $media_3],
        ];

        $this->postGraphQlBackOfficeUpload($attributes)
            ->assertJson([
                'data' => [
                    self::MUTATION => true,
                ]
            ])
        ;

        $model->refresh();

        $this->assertCount(3, $model->media);

        $this->assertEquals($model->media[0]->file_name, 'test_1.jpg');
        $this->assertEquals($model->media[0]->sort, 0);

        $this->assertEquals($model->media[1]->file_name, 'test_2.jpg');
        $this->assertEquals($model->media[1]->sort, 1);

        $this->assertEquals($model->media[2]->file_name, 'test_3.jpg');
        $this->assertEquals($model->media[2]->sort, 2);

        $this->assertDatabaseCount(Media::TABLE, 3);
        $this->assertDatabaseHas(Media::TABLE, ['model_type' => Product::MORPH_NAME, 'model_id' => $model->id]);
    }

    /** @test */
    public function success_add_media_to_product(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Product */
        $model = $this->productBuilder->create();

        $media_1 = UploadedFile::fake()->image('test_1.jpg');
        $media_2 = UploadedFile::fake()->image('test_2.jpg');
        $media_3 = UploadedFile::fake()->image('test_3.jpg');
        $media_4 = UploadedFile::fake()->image('test_4.jpg');

        $model->addMedia($media_1)
            ->withAttributes(['sort' => 0])
            ->toMediaCollection($model->getMediaCollectionName());
        $model->addMedia($media_2)
            ->withAttributes(['sort' => 1])
            ->toMediaCollection($model->getMediaCollectionName());

        $this->assertCount(2, $model->media);

        $attributes = [
            'operations' => sprintf(
                '{"query": "mutation ($media: [Upload!]!) {%s (model_id: %s, model_type: %s, media: $media) }"}',
                self::MUTATION,
                $model->id,
                Product::MORPH_NAME,
            ),
            'map' => '{ "media": ["variables.media"] }',
            'media' => [$media_3, $media_4],
        ];

        $this->postGraphQlBackOfficeUpload($attributes)
            ->assertJson([
                'data' => [
                    self::MUTATION => true,
                ]
            ])
        ;

        $model->refresh();

        $this->assertCount(4, $model->media);

        $this->assertEquals($model->media[0]->file_name, 'test_1.jpg');
        $this->assertEquals($model->media[0]->sort, 0);

        $this->assertEquals($model->media[1]->file_name, 'test_2.jpg');
        $this->assertEquals($model->media[1]->sort, 1);

        $this->assertEquals($model->media[2]->file_name, 'test_3.jpg');
        $this->assertEquals($model->media[2]->sort, 2);

        $this->assertEquals($model->media[3]->file_name, 'test_4.jpg');
        $this->assertEquals($model->media[3]->sort, 3);
    }

    /** @test */
    public function success_add_media_to_product_diff_sort(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Product */
        $model = $this->productBuilder->create();

        $media_1 = UploadedFile::fake()->image('test_1.jpg');
        $media_2 = UploadedFile::fake()->image('test_2.jpg');
        $media_3 = UploadedFile::fake()->image('test_3.jpg');
        $media_4 = UploadedFile::fake()->image('test_4.jpg');

        $model->addMedia($media_1)
            ->withAttributes(['sort' => 2])
            ->toMediaCollection($model->getMediaCollectionName());
        $model->addMedia($media_2)
            ->withAttributes(['sort' => 1])
            ->toMediaCollection($model->getMediaCollectionName());

        $this->assertCount(2, $model->media);

        $attributes = [
            'operations' => sprintf(
                '{"query": "mutation ($media: [Upload!]!) {%s (model_id: %s, model_type: %s, media: $media) }"}',
                self::MUTATION,
                $model->id,
                Product::MORPH_NAME,
            ),
            'map' => '{ "media": ["variables.media"] }',
            'media' => [$media_4, $media_3],
        ];

        $this->postGraphQlBackOfficeUpload($attributes)
            ->assertJson([
                'data' => [
                    self::MUTATION => true,
                ]
            ])
        ;

        $model->refresh();

        $this->assertCount(4, $model->media);

        $this->assertEquals($model->media[0]->file_name, 'test_2.jpg');
        $this->assertEquals($model->media[0]->sort, 1);

        $this->assertEquals($model->media[1]->file_name, 'test_1.jpg');
        $this->assertEquals($model->media[1]->sort, 2);

        $this->assertEquals($model->media[2]->file_name, 'test_4.jpg');
        $this->assertEquals($model->media[2]->sort, 3);

        $this->assertEquals($model->media[3]->file_name, 'test_3.jpg');
        $this->assertEquals($model->media[3]->sort, 4);
    }

    /** @test */
    public function fail_not_support_model(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Product */
        $model = $this->productBuilder->create();

        $media_1 = UploadedFile::fake()->image('test_1.jpg');

        $attributes = [
            'operations' => sprintf(
                '{"query": "mutation ($media: [Upload!]!) {%s (model_id: %s, model_type: %s, media: $media) }"}',
                self::MUTATION,
                $model->id,
                'fail',
            ),
            'map' => '{ "media": ["variables.media"] }',
            'media' => [$media_1],
        ];

        $res = $this->postGraphQlBackOfficeUpload($attributes);

        $this->assertErrorMessage($res, "Field \"mediaUpload\" argument \"model_type\" requires type MediaModelsTypeEnum, found fail.");
    }
}
