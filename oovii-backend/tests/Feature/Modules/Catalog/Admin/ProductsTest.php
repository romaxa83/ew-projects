<?php

namespace Tests\Feature\Modules\Catalog\Admin;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use WezomCms\Catalog\CatalogServiceProvider;
use WezomCms\Catalog\Models\Product;
use WezomCms\Core\Contracts\PermissionsContainerInterface;
use WezomCms\Core\Models\Administrator;

class ProductsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_provider_can_edit_only_his_own_products(): void
    {
        $this->registerPermissions();

        /** @var Administrator $admin1 */
        $admin1 = Administrator::factory()->create([ 'super_admin' => false ]);
        /** @var Product $product1 */
        $product1 = Product::factory()->create([ 'cost' => 100, 'provider_id' => $admin1->id, ]);

        /** @var Administrator $admin2 */
        $admin2 = Administrator::factory()->create([ 'super_admin' => false ]);
        /** @var Product $product2 */
        $product2 = Product::factory()->create([ 'cost' => 200, 'provider_id' => $admin2->id, ]);

        $this->loginAsProvider($admin1, ['products.edit', 'products.delete']);

        $productData = $this->getProductData($product2);
        $productData['cost'] = 150;

        $this->put(route('admin.products.update', [ 'product' => $product2->id ]), $productData)
            ->assertNotFound();
        $this->put(route('admin.products.update', [ 'product' => $product1->id ]), $productData)
            ->assertStatus(302);
    }

    public function test_provider_can_delete_only_his_own_products(): void
    {
        $this->registerPermissions();

        /** @var Administrator $admin1 */
        $admin1 = Administrator::factory()->create([ 'super_admin' => false ]);
        /** @var Product $product1 */
        $product1 = Product::factory()->create([ 'cost' => 100, 'provider_id' => $admin1->id, ]);

        /** @var Administrator $admin2 */
        $admin2 = Administrator::factory()->create([ 'super_admin' => false ]);
        /** @var Product $product2 */
        $product2 = Product::factory()->create([ 'cost' => 200, 'provider_id' => $admin2->id, ]);

        $this->loginAsProvider($admin1, ['products.edit', 'products.delete']);

        $this->delete(route('admin.products.destroy', [ 'product' => $product2->id ]))
            ->assertNotFound();
        $this->delete(route('admin.products.destroy', [ 'product' => $product1->id ]))
            ->assertStatus(302);
    }

    private function getProductData(Product $product): array
    {
        $data = [
            'published' => $product->published,
            'cost' => $product->cost,
            'amount' => $product->amount,
            'amount_one_user' => $product->amount_one_user,
            'provider_id' => $product->provider_id,
            'weight' => $product->weight,
            'popular' => (bool)$product->popular,
            'best_price' => (bool)$product->best_price,
        ];

        foreach ($product->translations as $translation) {
            $data[$translation->locale] = [
                'name' => $translation->name,
            ];
        }

        return $data;
    }

    private function registerPermissions(): void
    {
        /** @var CatalogServiceProvider $provider */
        $provider = app()->resolveProvider(CatalogServiceProvider::class);
        $permissions = resolve(PermissionsContainerInterface::class);
        $provider->permissions($permissions);
    }
}




