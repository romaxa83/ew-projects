<?php

namespace Tests\Feature\Modules\Catalog\Admin;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use WezomCms\Catalog\Models\Product;
use WezomCms\Core\Contracts\PermissionsContainerInterface;
use WezomCms\Core\Models\Administrator;
use WezomCms\ProductReviews\Models\ProductReview;
use WezomCms\ProductReviews\ProductReviewsServiceProvider;

class ProductReviewsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_provider_can_edit_only_reviews_for_his_own_products(): void
    {
        $this->registerPermissions();

        /** @var Administrator $admin1 */
        $admin1 = Administrator::factory()->create([ 'super_admin' => false ]);
        /** @var Product $product1 */
        $product1 = Product::factory()->create([ 'cost' => 100, 'provider_id' => $admin1->id, ]);
        /** @var ProductReview $review1 */
        $review1 = ProductReview::factory()->create([ 'product_id' => $product1->id, 'admin_answer' => false ]);

        /** @var Administrator $admin2 */
        $admin2 = Administrator::factory()->create([ 'super_admin' => false ]);
        /** @var Product $product2 */
        $product2 = Product::factory()->create([ 'cost' => 200, 'provider_id' => $admin2->id, ]);
        /** @var ProductReview $review2 */
        $review2 = ProductReview::factory()->create([ 'product_id' => $product2->id, 'admin_answer' => false ]);

        $this->loginAsProvider($admin1, ['product-reviews.edit', 'product-reviews.delete']);

        $reviewData = $this->getReviewData($review2);
        $reviewData['like'] = !$reviewData['like'];

        $this->put(route('admin.product-reviews.update', [ 'product_review' => $review2->id ]), $reviewData)
            ->assertNotFound();
        $this->put(route('admin.product-reviews.update', [ 'product_review' => $review1->id ]), $reviewData)
            ->assertStatus(302);
    }

    public function test_provider_can_delete_only_his_own_products(): void
    {
        $this->registerPermissions();

        /** @var Administrator $admin1 */
        $admin1 = Administrator::factory()->create([ 'super_admin' => false ]);
        /** @var Product $product1 */
        $product1 = Product::factory()->create([ 'cost' => 100, 'provider_id' => $admin1->id, ]);
        /** @var ProductReview $review1 */
        $review1 = ProductReview::factory()->create([ 'product_id' => $product1->id, 'admin_answer' => false ]);

        /** @var Administrator $admin2 */
        $admin2 = Administrator::factory()->create([ 'super_admin' => false ]);
        /** @var Product $product2 */
        $product2 = Product::factory()->create([ 'cost' => 200, 'provider_id' => $admin2->id, ]);
        /** @var ProductReview $review2 */
        $review2 = ProductReview::factory()->create([ 'product_id' => $product2->id, 'admin_answer' => false ]);

        $this->loginAsProvider($admin1, ['product-reviews.edit', 'product-reviews.delete']);

        $this->delete(route('admin.product-reviews.destroy', [ 'product_review' => $review2->id ]))
            ->assertNotFound();
        $this->delete(route('admin.product-reviews.destroy', [ 'product_review' => $review1->id ]))
            ->assertStatus(302);
    }

    private function getReviewData(ProductReview $review): array
    {
        return [
            'published' => $review->published,
            'product_id' => $review->product_id,
            'already_bought' => $review->already_bought,
            'admin_answer' => $review->admin_answer,
            'like' => $review->like,
            'text' => $review->text,
            'name' => $review->name,
            'email' => $review->email,
        ];
    }

    private function registerPermissions(): void
    {
        /** @var ProductReviewsServiceProvider $provider */
        $provider = app()->resolveProvider(ProductReviewsServiceProvider::class);
        $permissions = resolve(PermissionsContainerInterface::class);
        $provider->permissions($permissions);
    }
}




