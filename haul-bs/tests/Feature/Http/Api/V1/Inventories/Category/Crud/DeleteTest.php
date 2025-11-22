<?php

namespace Feature\Http\Api\V1\Inventories\Category\Crud;

use App\Events\Events\Inventories\Categories\DeleteCategoryEvent;
use App\Events\Listeners\Inventories\Categories\SyncEComDeleteCategoryListener;
use App\Foundations\Modules\Media\Models\Media;
use App\Foundations\Modules\Seo\Models\Seo;
use App\Models\Inventories\Category;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\Builders\Inventories\CategoryBuilder;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Seo\SeoBuilder;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected CategoryBuilder $categoryBuilder;
    protected SeoBuilder $seoBuilder;
    protected InventoryBuilder $inventoryBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->categoryBuilder = resolve(CategoryBuilder::class);
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->seoBuilder = resolve(SeoBuilder::class);
    }

    /** @test */
    public function success_delete()
    {
        Event::fake([DeleteCategoryEvent::class]);

        $this->loginUserAsSuperAdmin();

        /** @var $model Category */
        $model = $this->categoryBuilder->create();

        $this->seoBuilder->model($model)->create();

        $id = $model->id;

        $this->deleteJson(route('api.v1.inventories.category.delete', ['id' => $model->id]))
            ->assertNoContent()
        ;

        $this->assertFalse(Category::query()->where('id', $id)->exists());
        $this->assertFalse(Seo::query()->where('model_id', $id)->where('model_type', Category::MORPH_NAME)->exists());

        Event::assertDispatched(fn (DeleteCategoryEvent $event) =>
            $event->getModel()->id === (int)$id
        );
        Event::assertListening(DeleteCategoryEvent::class, SyncEComDeleteCategoryListener::class);
    }

    /** @test */
    public function success_delete_has_trashed_inventory()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Category */
        $model = $this->categoryBuilder->create();

        $inventory_1 = $this->inventoryBuilder->category($model)->setDate('deleted_at')->create();
        $inventory_2 = $this->inventoryBuilder->category($model)->setDate('deleted_at')->create();

        $id = $model->id;

        $this->deleteJson(route('api.v1.inventories.category.delete', ['id' => $model->id]))
            ->assertNoContent()
        ;

        $this->assertFalse(Category::query()->where('id', $id)->exists());

        $inventory_1->refresh();
        $inventory_2->refresh();

        $this->assertNull($inventory_1->category_id);
        $this->assertNull($inventory_2->category_id);
    }

    /** @test */
    public function success_delete_with_img()
    {
        Storage::fake(self::FAKE_DISK_STORAGE);

        $this->loginUserAsSuperAdmin();

        /** @var $model Category */
        $model = $this->categoryBuilder->create();

        $img = UploadedFile::fake()->image('img.png');
        $seo = $this->seoBuilder->model($model)->image($img)->create();

        $seoId = $seo->id;

        $this->deleteJson(route('api.v1.inventories.category.delete', ['id' => $model->id]))
            ->assertNoContent()
        ;

        $this->assertFalse(Media::query()->where('model_id', $seoId)->where('model_type', Seo::MORPH_NAME)->exists());
    }

    /** @test */
    public function fail_delete_has_inventories()
    {
        Event::fake([DeleteCategoryEvent::class]);

        $this->loginUserAsSuperAdmin();

        /** @var $model Category */
        $model = $this->categoryBuilder->create();

        $this->inventoryBuilder->category($model)->create();

        $id = $model->id;

        $res = $this->deleteJson(route('api.v1.inventories.category.delete', ['id' => $model->id]))
        ;

        $link = str_replace('{id}', $model->id, config('routes.front.inventories_with_category_filter_url'));

        self::assertErrorMsg(
            $res,
            __("exceptions.inventories.category.has_inventories", ['link' => $link]),
            Response::HTTP_UNPROCESSABLE_ENTITY
        );

        $this->assertTrue(Category::query()->where('id', $id)->exists());

        Event::assertNotDispatched(fn (DeleteCategoryEvent $event) =>
            $event->getModel()->id === (int)$id
        );
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->deleteJson(route('api.v1.inventories.category.delete', ['id' => 0]));

        self::assertErrorMsg($res, __("exceptions.inventories.category.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Category */
        $model = $this->categoryBuilder->create();

        $res = $this->deleteJson(route('api.v1.inventories.category.delete', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Category */
        $model = $this->categoryBuilder->create();

        $res = $this->deleteJson(route('api.v1.inventories.category.delete', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
