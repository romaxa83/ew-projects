<?php

namespace Tests\Feature\Modules\Catalog\V1\Product;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\ProductBuilder;
use Tests\Builders\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class AddToWishlistTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    private $productBuilder;
    private $userBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->productBuilder = app(ProductBuilder::class);
        $this->userBuilder = app(UserBuilder::class);
    }

    /** @test */
    public function success(): void
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $product = $this->productBuilder->create();

        $this->assertEmpty($user->wishlist);

        $this->postJson(route('api.v1.mobile.products.add-to-wishlist', [
            'id' => $product->id
        ]))
            ->assertOk()
            ->assertJson($this->structureSucessResponse(
                __('cms-catalog::admin.products.add to wishlist')
            ));

        $user->refresh();

        $this->assertNotEmpty($user->wishlist);
        $this->assertTrue(in_array($product->id, $user->wishlist()->pluck('id')->toArray()));
    }

    /** @test */
    public function fail_not_found_product(): void
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->assertEmpty($user->wishlist);

        $this->postJson(route('api.v1.mobile.products.add-to-wishlist', [
            'id' => 1
        ]))
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson($this->structureErrorResponse(
                __('cms-catalog::admin.products.exception.not found by id', [
                    "id" => 1
                ])
            ));
    }

    /** @test */
    public function fail_product_exist_in_wishlist(): void
    {
        $product = $this->productBuilder->create();
        $user = $this->userBuilder->setWishlist($product->id)->create();
        $this->loginAsUser($user);

        $this->assertTrue(in_array($product->id, $user->wishlist()->pluck('id')->toArray()));

        $this->postJson(route('api.v1.mobile.products.add-to-wishlist', [
            'id' => $product->id
        ]))
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson($this->structureErrorResponse(
                __('cms-catalog::admin.products.exception.exist in wishlist', [
                    "id" => $product->id
                ])
            ));
    }

    /** @test */
    public function not_auth(): void
    {
        $this->postJson(route('api.v1.mobile.products.add-to-wishlist', [
            'id' => 1
        ]))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse(__("cms-core::site.Unauthenticated")));
    }
}




