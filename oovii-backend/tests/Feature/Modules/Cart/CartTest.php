<?php


namespace Tests\Feature\Modules\Cart;


use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\Builders\ProductBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;
use WezomCms\Catalog\Models\Product;
use WezomCms\Core\Models\Administrator;
use WezomCms\Orders\Contracts\CartInterface;
use WezomCms\Orders\Jobs\ClearOldCarts;
use WezomCms\Orders\Models\Cart;
use WezomCms\Orders\Models\CartItem;

class CartTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    private ProductBuilder $productBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->productBuilder = app(ProductBuilder::class);
    }

    public function test_it_stores_cart_hash_in_config(): void
    {
        $cartHash = sha1(microtime() . Str::random());
        $this->getJson(route('api.v1.mobile.cart.get'), [ 'Cart-hash' => $cartHash ]);

        self::assertEquals($cartHash, config('cms.orders.cart.hash'));
    }

    public function test_it_returns_empty_cart_on_request_without_cart_hash(): void
    {
        $res = $this->getJson(route('api.v1.mobile.cart.get'))
            ->assertOk()
            ->assertJsonStructure($this->structureResource([
                'hash',
                'items',
                'total',
                'sub_total',
                'items_quantity',
            ]));

        $cartData = $res->json('data');

        self::assertEquals([], $cartData['items']);
        self::assertEquals(0, $cartData['total']);
        self::assertEquals(0, $cartData['sub_total']);
        self::assertEquals(0, $cartData['items_quantity']);
    }

    public function test_it_returns_cart_by_given_cart_hash(): void
    {
        $cartHash = sha1(microtime() . Str::random());
        $res = $this->getJson(route('api.v1.mobile.cart.get'), [ 'Cart-hash' => $cartHash ])
            ->assertOk();

        $cartData = $res->json('data');

        self::assertEquals($cartHash, $cartData['hash']);
    }

    public function test_it_returns_cart_with_items_by_given_cart_hash(): void
    {
        /** @var Product $product1 */
        $product1 = Product::factory()->create([ 'cost' => 500, 'cost_discount' => 400 ]);
        /** @var Product $product2 */
        $product2 = Product::factory()->create([ 'cost' => 200, 'cost_discount' => 0.0 ]);

        $cartHash = sha1(microtime() . Str::random());
        config([ 'cms.orders.cart.hash' => $cartHash ]);

        $cart = resolve(CartInterface::class);
        $cart->add($product1, 1);
        $cart->add($product2, 2);

        $res = $this->getJson(route('api.v1.mobile.cart.get'), [ 'Cart-hash' => $cartHash ])
            ->assertOk();

        $cartData = $res->json('data');

        self::assertEquals($cartHash, $cartData['hash']);
        self::assertEquals(800, $cartData['total']);
        self::assertEquals(800, $cartData['sub_total']);
        self::assertEquals(3, $cartData['items_quantity']);
        self::assertCount(2, $cartData['items']);
        self::assertEquals($product1->id, $cartData['items'][0]['product']['id']);
        self::assertEquals(1, $cartData['items'][0]['quantity']['value']);
        self::assertEquals(400, $cartData['items'][0]['sub_total']);
        self::assertEquals(500, $cartData['items'][0]['crossed_out_sub_total']);
        self::assertEquals($product2->id, $cartData['items'][1]['product']['id']);
        self::assertEquals(2, $cartData['items'][1]['quantity']['value']);
        self::assertEquals(400, $cartData['items'][1]['sub_total']);
        self::assertEquals(400, $cartData['items'][1]['crossed_out_sub_total']);
    }

    public function test_it_adds_products_to_cart(): void
    {
        /** @var Product $product1 */
        $product1 = Product::factory()->create([ 'cost' => 500, 'cost_discount' => 400 ]);
        /** @var Product $product2 */
        $product2 = Product::factory()->create([ 'cost' => 200, 'cost_discount' => 0.0 ]);

        $requestData = [
            'product_id' => $product1->id,
            'quantity' => 2,
        ];

        $res = $this->postJson(route('api.v1.mobile.cart.add'), $requestData)
            ->assertOk();

        $cartData = $res->json('data');

        $cartHash = $cartData['hash'];

        self::assertEquals(800, $cartData['total']);
        self::assertEquals(800, $cartData['sub_total']);
        self::assertEquals(2, $cartData['items_quantity']);
        self::assertCount(1, $cartData['items']);
        self::assertEquals($product1->id, $cartData['items'][0]['product']['id']);
        self::assertEquals(2, $cartData['items'][0]['quantity']['value']);
        self::assertEquals(800, $cartData['items'][0]['sub_total']);
        self::assertEquals(1000, $cartData['items'][0]['crossed_out_sub_total']);

        $requestData = [
            'product_id' => $product2->id,
            'quantity' => 3,
        ];

        $res = $this->postJson(route('api.v1.mobile.cart.add'), $requestData, [ 'Cart-hash' => $cartHash ])
            ->assertOk();

        $cartData = $res->json('data');

        self::assertEquals($cartHash, $cartData['hash']);
        self::assertEquals(1400, $cartData['total']);
        self::assertEquals(1400, $cartData['sub_total']);
        self::assertEquals(5, $cartData['items_quantity']);
        self::assertCount(2, $cartData['items']);
        self::assertEquals($product1->id, $cartData['items'][0]['product']['id']);
        self::assertEquals(2, $cartData['items'][0]['quantity']['value']);
        self::assertEquals(800, $cartData['items'][0]['sub_total']);
        self::assertEquals(1000, $cartData['items'][0]['crossed_out_sub_total']);
        self::assertEquals($product2->id, $cartData['items'][1]['product']['id']);
        self::assertEquals(3, $cartData['items'][1]['quantity']['value']);
        self::assertEquals(600, $cartData['items'][1]['sub_total']);
        self::assertEquals(600, $cartData['items'][1]['crossed_out_sub_total']);
    }

    public function test_user_can_add_to_cart_product_which_already_in_cart(): void
    {
        /** @var Product $product1 */
        $product1 = Product::factory()->create([ 'cost' => 500, 'cost_discount' => 400 ]);
        /** @var Product $product2 */
        $product2 = Product::factory()->create([ 'cost' => 200, 'cost_discount' => 0.0 ]);

        $cartHash = sha1(microtime() . Str::random());
        config([ 'cms.orders.cart.hash' => $cartHash ]);

        $cart = resolve(CartInterface::class);
        $cart->add($product1, 1);
        $cart->add($product2, 2);

        $requestData = [
            'product_id' => $product1->id,
            'quantity' => 2,
        ];

        $res = $this->postJson(route('api.v1.mobile.cart.add'), $requestData)
            ->assertOk();

        $cartData = $res->json('data');

        self::assertEquals(1600, $cartData['total']);
        self::assertEquals(1600, $cartData['sub_total']);
        self::assertEquals(5, $cartData['items_quantity']);
        self::assertCount(2, $cartData['items']);
        self::assertEquals($product1->id, $cartData['items'][0]['product']['id']);
        self::assertEquals(3, $cartData['items'][0]['quantity']['value']);
        self::assertEquals(1200, $cartData['items'][0]['sub_total']);
        self::assertEquals(1500, $cartData['items'][0]['crossed_out_sub_total']);
        self::assertEquals($product2->id, $cartData['items'][1]['product']['id']);
        self::assertEquals(2, $cartData['items'][1]['quantity']['value']);
        self::assertEquals(400, $cartData['items'][1]['sub_total']);
        self::assertEquals(400, $cartData['items'][1]['crossed_out_sub_total']);
    }

    public function test_it_returns_validation_error_on_adding_product_to_cart(): void
    {
        /** @var Product $product1 */
        $product1 = Product::factory()->create([ 'cost' => 500 ]);

        $requestData = [
            'quantity' => 2,
        ];

        $this->postJson(route('api.v1.mobile.cart.add'), $requestData)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson($this->structureErrorResponse(
                __('validation.required', [ 'attribute' => __('cms-orders::site.cart.Product id') ])
            ));

        $requestData = [
            'product_id' => $product1->id + 1,
            'quantity' => 2,
        ];

        $this->postJson(route('api.v1.mobile.cart.add'), $requestData)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson($this->structureErrorResponse(
                __('validation.exists', [ 'attribute' => __('cms-orders::site.cart.Product id') ])
            ));

        $requestData = [
            'product_id' => $product1->id,
            'quantity' => 0,
        ];

        $this->postJson(route('api.v1.mobile.cart.add'), $requestData)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson($this->structureErrorResponse(
                __('validation.min.numeric', [
                    'attribute' => __('cms-orders::site.cart.Quantity'),
                    'min' => 1,
                ])
            ));
    }

    public function test_it_doesnt_add_to_cart_products_with_have_no_enough_amount(): void
    {
        /** @var Product $product */
        $product = Product::factory()->create([
            'cost' => 500,
            'amount' => 10,
            'amount_one_user' => 5,
        ]);

        $requestData = [
            'product_id' => $product->id,
            'quantity' => 15,
        ];

        $res = $this->postJson(route('api.v1.mobile.cart.add'), $requestData)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        self::assertEquals(__('cms-order::site.errors.Forbidden quantity'), $res->json('data'));

        $requestData['quantity'] = 8;

        $res = $this->postJson(route('api.v1.mobile.cart.add'), $requestData)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        self::assertEquals(__('cms-order::site.errors.Forbidden quantity'), $res->json('data'));

        $product->available = false;
        $product->save();

        $requestData['quantity'] = 3;

        $res = $this->postJson(route('api.v1.mobile.cart.add'), $requestData)
            ->assertStatus(Response::HTTP_NOT_FOUND);

        self::assertEquals(__('cms-order::site.errors.Product not available for purchase'), $res->json('data'));
    }

    public function test_it_can_set_exists_cart_item_quantity(): void
    {
        /** @var Product $product1 */
        $product1 = Product::factory()->create([ 'cost' => 500, 'cost_discount' => 400 ]);
        /** @var Product $product2 */
        $product2 = Product::factory()->create([ 'cost' => 200, 'cost_discount' => 0 ]);

        $cartHash = sha1(microtime() . Str::random());
        config([ 'cms.orders.cart.hash' => $cartHash ]);

        $cart = resolve(CartInterface::class);
        $cart->add($product1, 1);
        $cart->add($product2, 2);

        $requestData = [
            'unique_id' => $cart->content()->first()->getUniqueId(),
            'quantity' => 3,
        ];

        $res = $this->postJson(route('api.v1.mobile.cart.set-quantity'), $requestData, [ 'Cart-hash' => $cartHash ])
            ->assertOk();

        $cartData = $res->json('data');

        self::assertEquals($cartHash, $cartData['hash']);
        self::assertEquals(1600, $cartData['total']);
        self::assertEquals(1600, $cartData['sub_total']);
        self::assertEquals(5, $cartData['items_quantity']);
        self::assertCount(2, $cartData['items']);
        self::assertEquals($product1->id, $cartData['items'][0]['product']['id']);
        self::assertEquals(3, $cartData['items'][0]['quantity']['value']);
        self::assertEquals(1200, $cartData['items'][0]['sub_total']);
        self::assertEquals(1500, $cartData['items'][0]['crossed_out_sub_total']);
        self::assertEquals($product2->id, $cartData['items'][1]['product']['id']);
        self::assertEquals(2, $cartData['items'][1]['quantity']['value']);
        self::assertEquals(400, $cartData['items'][1]['sub_total']);
        self::assertEquals(400, $cartData['items'][1]['crossed_out_sub_total']);
    }

    public function test_it_can_remove_cart_item_from_cart(): void
    {
        /** @var Product $product1 */
        $product1 = Product::factory()->create([ 'cost' => 500, 'cost_discount' => 400 ]);
        /** @var Product $product2 */
        $product2 = Product::factory()->create([ 'cost' => 200, 'cost_discount' => 0 ]);

        $cartHash = sha1(microtime() . Str::random());
        config([ 'cms.orders.cart.hash' => $cartHash ]);

        $cart = resolve(CartInterface::class);
        $cart->add($product1, 1);
        $cart->add($product2, 2);

        $uniqueId = $cart->content()->first()->getUniqueId();

        $res = $this->deleteJson(route('api.v1.mobile.cart.remove', [ 'uniqueId' => $uniqueId ]), [ 'Cart-hash' => $cartHash ])
            ->assertOk();

        $cartData = $res->json('data');

        self::assertEquals($cartHash, $cartData['hash']);
        self::assertEquals(400, $cartData['total']);
        self::assertEquals(400, $cartData['sub_total']);
        self::assertEquals(2, $cartData['items_quantity']);
        self::assertCount(1, $cartData['items']);
        self::assertEquals($product2->id, $cartData['items'][0]['product']['id']);
        self::assertEquals(2, $cartData['items'][0]['quantity']['value']);
        self::assertEquals(400, $cartData['items'][0]['sub_total']);
    }

    public function test_it_returns_cart_with_items_separated_by_provider(): void
    {
        $this->loginAsUser();
        /** @var Administrator $provider1 */
        $provider1 = Administrator::factory()->create([ 'super_admin' => false ]);
        /** @var Administrator $provider2 */
        $provider2 = Administrator::factory()->create([ 'super_admin' => false ]);

        /** @var Product $product1 */
        $product1 = Product::factory()->create([ 'cost' => 500, 'cost_discount' => 400, 'provider_id' => $provider1->id ]);
        /** @var Product $product2 */
        $product2 = Product::factory()->create([ 'cost' => 200, 'cost_discount' => 0.0, 'provider_id' => $provider2->id ]);
        /** @var Product $product3 */
        $product3 = Product::factory()->create([ 'cost' => 250, 'cost_discount' => 0.0, 'provider_id' => $provider2->id ]);

        $cartHash = sha1(microtime() . Str::random());
        config([ 'cms.orders.cart.hash' => $cartHash ]);

        $cart = resolve(CartInterface::class);
        $cart->add($product1, 1);
        $cart->add($product2, 2);
        $cart->add($product3, 3);

        $res = $this->getJson(route('api.v1.mobile.cart.separated'), [ 'Cart-hash' => $cartHash ])
            ->assertOk();

        $cartData = $res->json('data');

        self::assertEquals($cartHash, $cartData['hash']);
        self::assertEquals(1550, $cartData['total']);
        self::assertEquals(1550, $cartData['sub_total']);
        self::assertEquals(6, $cartData['items_quantity']);
        self::assertCount(2, $cartData['items']);

        [$group1, $group2] = $cartData['items'];

        self::assertCount(1, $group1);
        self::assertCount(2, $group2);
        self::assertEquals($product1->id, $group1[0]['product']['id']);
        self::assertEquals(1, $group1[0]['quantity']['value']);
        self::assertEquals(400, $group1[0]['sub_total']);
        self::assertEquals(500, $group1[0]['crossed_out_sub_total']);

        self::assertEquals($product2->id, $group2[0]['product']['id']);
        self::assertEquals(2, $group2[0]['quantity']['value']);
        self::assertEquals(400, $group2[0]['sub_total']);
        self::assertEquals(400, $group2[0]['crossed_out_sub_total']);

        self::assertEquals($product3->id, $group2[1]['product']['id']);
        self::assertEquals(3, $group2[1]['quantity']['value']);
        self::assertEquals(750, $group2[1]['sub_total']);
        self::assertEquals(750, $group2[1]['crossed_out_sub_total']);
    }

    public function test_it_clears_old_carts(): void
    {
        /** @var Cart $cart1 */
        $cart1 = Cart::factory()->create([ 'created_at' => Carbon::now()->subWeek() ]);
        /** @var Cart $cart2 */
        $cart2 = Cart::factory()->create([ 'created_at' => Carbon::now()->subDays(2) ]);
        /** @var Cart $cart3 */
        $cart3 = Cart::factory()->create([ 'created_at' => Carbon::now() ]);

        $cartItem = new CartItem();
        $cartItem->cart_id = $cart1->id;
        $cartItem->unique_id = Str::random();
        $cartItem->save();

        $this->assertDatabaseCount(Cart::TABLE, 3);

        ClearOldCarts::dispatchSync();

        $this->assertDatabaseCount(Cart::TABLE, 3);
        $this->assertDatabaseHas(Cart::TABLE, [ 'id' => $cart1->id ]);
        $this->assertDatabaseMissing(Cart::TABLE, [ 'id' => $cart2->id ]);
        $this->assertDatabaseHas(Cart::TABLE, [ 'id' => $cart3->id ]);
    }

    public function test_it_can_clear_cart(): void
    {
        /** @var Product $product1 */
        $product1 = Product::factory()->create([ 'cost' => 500, 'cost_discount' => 400 ]);
        /** @var Product $product2 */
        $product2 = Product::factory()->create([ 'cost' => 200, 'cost_discount' => 0 ]);

        $cartHash = sha1(microtime() . Str::random());
        config([ 'cms.orders.cart.hash' => $cartHash ]);

        $cart = resolve(CartInterface::class);
        $cart->add($product1, 1);
        $cart->add($product2, 2);

        $res = $this->getJson(route('api.v1.mobile.cart.clear'), [ 'Cart-hash' => $cartHash ])
            ->assertOk();

        $cartData = $res->json('data');

        self::assertEquals($cartHash, $cartData['hash']);
        self::assertEquals(0, $cartData['total']);
        self::assertEquals(0, $cartData['items_quantity']);
        self::assertCount(0, $cartData['items']);
    }

    public function test_it_cant_move_items_from_cart_to_wishlist_for_unauthenticated_user(): void
    {
        $cartHash = sha1(microtime() . Str::random());
        config([ 'cms.orders.cart.hash' => $cartHash ]);

        $this->getJson(route('api.v1.mobile.cart.to-wishlist'), [ 'Cart-hash' => $cartHash ])
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse(__("cms-core::site.Unauthenticated")));
    }

    public function test_it_can_move_cart_items_to_wishlist(): void
    {
        $user = $this->loginAsUser();

        /** @var Product $product1 */
        $product1 = Product::factory()->create([ 'cost' => 500, 'cost_discount' => 400 ]);
        /** @var Product $product2 */
        $product2 = Product::factory()->create([ 'cost' => 200, 'cost_discount' => 0 ]);
        /** @var Product $product3 */
        $product3 = Product::factory()->create([ 'cost' => 250, 'cost_discount' => 0 ]);

        $user->wishlist()->attach([ $product1->id, $product2->id ]);

        self::assertCount(2, $user->wishlist);

        $cartHash = sha1(microtime() . Str::random());
        config([ 'cms.orders.cart.hash' => $cartHash ]);

        $cart = resolve(CartInterface::class);
        $cart->add($product2, 1);
        $cart->add($product3, 2);

        $this->getJson(route('api.v1.mobile.cart.to-wishlist'), [ 'Cart-hash' => $cartHash ])
            ->assertOk();

        $user->refresh();

        self::assertCount(3, $user->wishlist);
    }

    public function test_it_doesnt_add_to_cart_products_more_than_amount_for_one_user(): void
    {
        /** @var Product $product */
        $product = Product::factory()->create([
            'cost' => 500,
            'amount' => 10,
            'amount_one_user' => 5,
        ]);

        $requestData = [
            'product_id' => $product->id,
            'quantity' => 3,
        ];

        $this->postJson(route('api.v1.mobile.cart.add'), $requestData)
            ->assertOk();

        $res = $this->postJson(route('api.v1.mobile.cart.add'), $requestData)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        self::assertEquals(__('cms-order::site.errors.Forbidden quantity'), $res->json('data'));
    }
}
