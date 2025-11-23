<?php

namespace Tests\Feature\Modules\Catalog\V1\Product;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\ProductBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;
use WezomCms\Catalog\Models\Product;

class ProductFlagsTest extends TestCase
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

    public function test_it_returns_product_flags(): void
    {
        /** @var Product $model */
        $model = $this->productBuilder
            ->setPopular()
            ->setBestPrice()
            ->setPrices(500.0, 400.0)
            ->create();

        $res = $this->getJson(route('api.v1.mobile.products.show', [
            'id' => $model->id
        ]))
            ->assertOk()
            ->assertJsonCount(3, 'data.flags');

        $flags = $res->json('data.flags');

        self::assertEquals('popular', $flags[0]['name']);
        self::assertEquals(config('cms.catalog.products.flags.colors.popular'), $flags[0]['color']);
        self::assertEquals(__('cms-catalog::site.flags.popular'), $flags[0]['text']);

        self::assertEquals('best_price', $flags[1]['name']);
        self::assertEquals(config('cms.catalog.products.flags.colors.best_price'), $flags[1]['color']);
        self::assertEquals(__('cms-catalog::site.flags.best price'), $flags[1]['text']);

        self::assertEquals('sale', $flags[2]['name']);
        self::assertEquals(config('cms.catalog.products.flags.colors.sale'), $flags[2]['color']);
        self::assertEquals(__('cms-catalog::site.catalog.Sale :percent', ['percent' => 20]), $flags[2]['text']);
    }
}




