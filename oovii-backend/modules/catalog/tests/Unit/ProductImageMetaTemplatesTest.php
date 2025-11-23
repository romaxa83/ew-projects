<?php

namespace WezomCms\Catalog\Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use WezomCms\Catalog\Models\Brand;
use WezomCms\Catalog\Models\Product;

class ProductImageMetaTemplatesTest extends TestCase
{
    use DatabaseMigrations;

    public function testGetImageAltByTemplate()
    {
        settings()->set('catalog-seo-templates.image-template.alt', 'foo [name], [brand]');

        /** @var Product $product */
        $product = Product::factory()->make(['name' => 'product_name', 'brand_id' => null]);

        $product->images()->make();

        $this->assertEquals('foo product_name,', $product->image_alt);
    }

    public function testGetAltByTemplateWithBrand()
    {
        settings()->set('catalog-seo-templates.image-template.alt', 'foo [name], [brand]');

        /** @var Product $product */
        $product = Product::factory()->make(['name' => 'product_name']);
        $brand = Brand::factory()->make(['name' => 'brand_name']);

        $product->brand()->associate($brand);

        $product->images()->make();

        $this->assertEquals('foo product_name, brand_name', $product->image_alt);
    }

    public function testGetImageTitleByTemplate()
    {
        settings()->set('catalog-seo-templates.image-template.title', 'bar [name], [brand]');

        /** @var Product $product */
        $product = Product::factory()->make(['name' => 'product_name', 'brand_id' => null]);

        $product->images()->make();

        $this->assertEquals('bar product_name,', $product->image_title);
    }

    public function testGetProductImageAlt()
    {
        settings()->set('catalog-seo-templates.image-template.alt', 'foo [name]');

        /** @var Product $product */
        $product = Product::factory()->create(['name' => 'product_name']);

        $product->images()->create(['alt' => 'image_alt', 'default' => true]);

        $this->assertEquals('image_alt', $product->image_alt);
    }

    public function testGetProductImageTitle()
    {
        settings()->set('catalog-seo-templates.image-template.title', 'bar [name]');

        /** @var Product $product */
        $product = Product::factory()->create(['name' => 'product_name']);

        $product->images()->create(['title' => 'image_title', 'default' => true]);

        $this->assertEquals('image_title', $product->image_title);
    }

    public function testGetAllImagesAltByTemplateWithNumber()
    {
        settings()->set('catalog-seo-templates.image-template.alt', 'foo [name]');

        /** @var Product $product */
        $product = Product::factory()->create(['name' => 'product_name']);

        for ($i = 1; $i <= 5; $i++) {
            $product->images()->create();
        }

        foreach ($product->images as $index => $image) {
            $alt = $image->altAttribute($product, $index + 1);

            if ($index === 0) {
                $this->assertSame('foo product_name', $alt);
            } else {
                $this->assertStringStartsWith('foo product_name ', $alt);
                $this->assertStringEndsWith($index + 1, $alt);
            }
        }
    }

    public function testGetTitleImagesAltByTemplateWithNumber()
    {
        settings()->set('catalog-seo-templates.image-template.title', 'bar [name]');

        /** @var Product $product */
        $product = Product::factory()->create(['name' => 'product_name']);

        for ($i = 1; $i <= 5; $i++) {
            $product->images()->create();
        }

        foreach ($product->images as $index => $image) {
            $title = $image->titleAttribute($product, $index + 1);

            if ($index === 0) {
                $this->assertEquals('bar product_name', $title);
            } else {
                $this->assertStringStartsWith('bar product_name ', $title);
                $this->assertStringEndsWith($index + 1, $title);
            }
        }
    }

    public function testImageAltPriorityAttribute()
    {
        settings()->set('catalog-seo-templates.image-template.alt', 'foo [name]');

        /** @var Product $product */
        $product = Product::factory()->create(['name' => 'product_name']);

        $product->images()->create(['alt' => 'image alt']);

        $this->assertEquals('image alt', $product->images->first()->altAttribute($product, 1));
    }
}
