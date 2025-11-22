<?php

namespace Tests\Feature\Mutations\BackOffice\Utilities\Media;

use App\GraphQL\Mutations\BackOffice\Utilities\Media\MediaDeleteMutation;
use App\Models\Catalog\Products\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Tests\Builders\Catalog\ProductBuilder;
use Tests\TestCase;

class MediaDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = MediaDeleteMutation::NAME;

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

        $model->addMedia($media_1)
            ->withAttributes(['sort' => 0])
            ->toMediaCollection($model->getMediaCollectionName());
        $model->addMedia($media_2)
            ->withAttributes(['sort' => 1])
            ->toMediaCollection($model->getMediaCollectionName());
        $model->addMedia($media_3)
            ->withAttributes(['sort' => 2])
            ->toMediaCollection($model->getMediaCollectionName());


        $this->assertCount(3, $model->media);

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->media[1]->id, Product::MORPH_NAME)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => true,
                ]
            ])
        ;

        $model->refresh();

        $this->assertCount(2, $model->media);

        $this->assertEquals($model->media[0]->file_name, 'test_1.jpg');
        $this->assertEquals($model->media[0]->sort, 0);

        $this->assertEquals($model->media[1]->file_name, 'test_3.jpg');
        $this->assertEquals($model->media[1]->sort, 2);
    }

    /** @test */
    public function fail_wrong_type(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Product */
        $model = $this->productBuilder->create();

        $media_1 = UploadedFile::fake()->image('test_1.jpg');

        $model->addMedia($media_1)
            ->toMediaCollection($model->getMediaCollectionName());

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->media[0]->id, "wrong")
        ])
        ;

        $this->assertErrorMessage($res, "Field \"mediaDelete\" argument \"model_type\" requires type MediaModelsTypeEnum, found wrong.");
    }

    /** @test */
    public function fail_wrong_id(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Product */
        $model = $this->productBuilder->create();

        $media_1 = UploadedFile::fake()->image('test_1.jpg');

        $model->addMedia($media_1)
            ->toMediaCollection($model->getMediaCollectionName());

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->media[0]->id + 2, Product::MORPH_NAME)
        ])
        ;

        $this->assertErrorMessage($res, "Internal server error");
    }

    protected function getQueryStr($id, $type): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    media_id: %s,
                    model_type: %s,
                )
            }',
            self::MUTATION,
            $id,
            $type
        );
    }
}

