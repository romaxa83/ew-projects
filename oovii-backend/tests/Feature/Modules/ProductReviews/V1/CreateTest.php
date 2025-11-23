<?php

namespace Tests\Feature\Modules\ProductReviews\V1;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\ProductBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;
use Tests\Unit\product_reviews\Dto\ReviewDtoTest;

class CreateTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    private $productBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->productBuilder = app(ProductBuilder::class);
    }

    /** @test */
    public function success(): void
    {
        $user = $this->loginAsUser();
        $product = $this->productBuilder->create();
        $product->refresh();
        $data = ReviewDtoTest::data();
        unset(
            $data['user_id'],
            $data['product_id']
        );

        self::assertEmpty($product->reviews);

        $this->postJson(route('api.v1.mobile.product.review.create', [
            'id' => $product->id
        ]), $data)
            ->assertCreated()
            ->assertJson($this->structureResource([
                'text' => $data['text'],
                'like' => 1,
                'userName' => $user->full_name,
            ]));

        $product->refresh();
        self::assertNotEmpty($product->reviews);

        self::assertEquals(1, $product->likes);
        self::assertEquals(0, $product->dislikes);
    }

    /** @test */
    public function success_dislike(): void
    {
        $this->loginAsUser();
        $product = $this->productBuilder->create();

        $data = ReviewDtoTest::data();
        $data['like'] = false;
        unset(
            $data['user_id'],
            $data['product_id']
        );

        $this->postJson(route('api.v1.mobile.product.review.create', [
            'id' => $product->id
        ]), $data)
            ->assertCreated()
            ->assertJson($this->structureResource([
                'text' => $data['text'],
                'like' => 0,
            ]));

        $product->refresh();

        self::assertEquals(0, $product->likes);
        self::assertEquals(1, $product->dislikes);
    }

    /** @test */
    public function not_found_product(): void
    {
        $this->loginAsUser();

        $data = ReviewDtoTest::data();
        unset(
            $data['user_id'],
            $data['product_id']
        );

        $this->postJson(route('api.v1.mobile.product.review.create', [
            'id' => 1
        ]), $data)
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson($this->structureErrorResponse(
                __('cms-catalog::admin.products.exception.not found by id', [
                    'id' => 1
                ])
            ));
    }

    /** @test */
    public function not_found_parent(): void
    {
        $this->loginAsUser();
        $product = $this->productBuilder->create();
        $data = ReviewDtoTest::data();
        $data['parent_id'] = 1;
        unset(
            $data['user_id'],
            $data['product_id']
        );

        $this->postJson(route('api.v1.mobile.product.review.create', [
            'id' => $product->id
        ]), $data)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson($this->structureErrorResponse(
                __('cms-product-reviews::admin.validation.parent_id.exists')
            ));
    }

    /** @test */
    public function not_auth(): void
    {
        $product = $this->productBuilder->create();
        $product->refresh();
        $data = ReviewDtoTest::data();
        unset(
            $data['user_id'],
            $data['product_id']
        );

        $this->postJson(route('api.v1.mobile.product.review.create', [
            'id' => $product->id
        ]), $data)
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse(__('cms-core::site.Unauthenticated')));
    }
}




