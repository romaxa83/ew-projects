<?php

namespace Tests\Feature\Modules\Catalog\V1\Product;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\Builders\ProductBuilder;
use Tests\Builders\ReviewBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;
use WezomCms\Catalog\Models\Product;
use WezomCms\ProductReviews\Models\ProductReview;

class ShowTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    private $productBuilder;
    private $reviewBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->productBuilder = app(ProductBuilder::class);
        $this->reviewBuilder = app(ReviewBuilder::class);
    }

    /** @test */
    public function success(): void
    {
        $model = $this->productBuilder->create();

        $this->reviewBuilder->setProductId($model->id)->create();
        $this->reviewBuilder->setProductId($model->id)->create();


        $res = $this->getJson(route('api.v1.mobile.products.show', [
            'id' => $model->id
        ]))
            ->assertOk()
            ->assertJsonCount(2, 'data.reviews')
            ->assertJsonStructure($this->structureResource([
                'id',
                'name',
                'description',
                'features' => [],
                'labels' => [],
                'cost',
                'costDiscount',
                'amount',
                'amountOneUser',
                'image',
                'images' => [],
                'groupKey' => [],
                'provider' => [],
                'moderator' => [],
                'createdAt' => [],
                'publishedAt' => [],
                'reviews' => [
                    '*' => [
                        'id',
                        'text',
                        'like',
                        'userName',
                        'createdAt',
                    ]
                ],
                'products' => [],
                'attributes' => [],
                'collections' => [],
            ]));

        self::assertEquals($model->id, $res->json('data.id'));
    }

    public function test_product_has_reviews_list(): void
    {
        /** @var Product $product */
        $product = Product::factory()->create();
        /** @var ProductReview $review1 */
        $review1 = ProductReview::factory()->create([
            'product_id' => $product->id,
            'parent_id' => null,
            'admin_answer' => false,
            'created_at' => Carbon::now()->subDays(10),
        ]);
        /** @var ProductReview $review2 */
        $review2 = ProductReview::factory()->create([
            'product_id' => $product->id,
            'parent_id' => null,
            'admin_answer' => false,
            'created_at' => Carbon::now()->subDays(2),
        ]);
        /** @var ProductReview $answer1 */
        $answer1 = ProductReview::factory()->create([
            'product_id' => $product->id,
            'admin_answer' => true,
            'parent_id' => $review1->id,
            'created_at' => Carbon::now()->subDays(8),
        ]);
        /** @var ProductReview $answer2 */
        $answer2 = ProductReview::factory()->create([
            'product_id' => $product->id,
            'admin_answer' => true,
            'parent_id' => $review1->id,
            'created_at' => Carbon::now()->subDays(5),
        ]);
        /** @var ProductReview $review3 */
        $review3 = ProductReview::factory()->create([
            'product_id' => $product->id,
            'parent_id' => null,
            'admin_answer' => false,
            'created_at' => Carbon::now()->subDays(5),
        ]);
        ProductReview::factory()->create([
            'product_id' => $product->id,
            'admin_answer' => true,
            'parent_id' => $review1->id,
            'published' => false,
        ]);

        $res = $this->getJson(route('api.v1.mobile.products.show', ['id' => $product->id]));

        $reviewsData = $res->json('data.reviews');

        self::assertCount(3, $reviewsData);
        self::assertEquals($review2->id, $reviewsData[0]['id']);
        self::assertEquals($review3->id, $reviewsData[1]['id']);
        self::assertEquals($review1->id, $reviewsData[2]['id']);
        self::assertCount(0, $reviewsData[0]['answers']);
        self::assertCount(0, $reviewsData[1]['answers']);
        self::assertCount(2, $reviewsData[2]['answers']);
        self::assertEquals($answer2->id, $reviewsData[2]['answers'][0]['id']);
        self::assertEquals($answer1->id, $reviewsData[2]['answers'][1]['id']);
    }
}




