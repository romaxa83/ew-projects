<?php

namespace Feature\Http\Api\V1\Inventories\Brand\Crud;

use App\Events\Events\Inventories\Brands\DeleteBrandEvent;
use App\Events\Listeners\Inventories\Brands\SyncEComDeleteBrandListener;
use App\Foundations\Modules\Seo\Models\Seo;
use App\Models\Inventories\Brand;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Inventories\BrandBuilder;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Seo\SeoBuilder;
use Tests\TestCase;
use Illuminate\Support\Facades\Event;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected BrandBuilder $brandBuilder;
    protected SeoBuilder $seoBuilder;
    protected InventoryBuilder $inventoryBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->brandBuilder = resolve(BrandBuilder::class);
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->seoBuilder = resolve(SeoBuilder::class);
    }

    /** @test */
    public function success_delete()
    {
        Event::fake([DeleteBrandEvent::class]);

        $this->loginUserAsSuperAdmin();

        /** @var $model Brand */
        $model = $this->brandBuilder->create();

        $this->seoBuilder->model($model)->create();

        $id = $model->id;

        $this->deleteJson(route('api.v1.inventories.brand.delete', ['id' => $model->id]))
            ->assertNoContent()
        ;

        $this->assertFalse(Brand::query()->where('id', $id)->exists());
        $this->assertFalse(Seo::query()->where('model_id', $id)->where('model_type', Brand::MORPH_NAME)->exists());

        Event::assertDispatched(fn (DeleteBrandEvent $event) =>
            $event->getModel()->id === (int)$id
        );
        Event::assertListening(DeleteBrandEvent::class, SyncEComDeleteBrandListener::class);
    }

    /** @test */
    public function fail_delete_has_inventories()
    {
        Event::fake([DeleteBrandEvent::class]);

        $this->loginUserAsSuperAdmin();

        /** @var $model Brand */
        $model = $this->brandBuilder->create();

        $this->inventoryBuilder->brand($model)->create();

        $id = $model->id;

        $res = $this->deleteJson(route('api.v1.inventories.brand.delete', ['id' => $model->id]))
        ;

        self::assertErrorMsg(
            $res,
            __("exceptions.inventories.brand.has_related_entities"),
            Response::HTTP_UNPROCESSABLE_ENTITY
        );

        $this->assertTrue(Brand::query()->where('id', $id)->exists());

        Event::assertNotDispatched(fn (DeleteBrandEvent $event) =>
            $event->getModel()->id === (int)$id
        );
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->deleteJson(route('api.v1.inventories.brand.delete', ['id' => 0]));

        self::assertErrorMsg($res, __("exceptions.inventories.brand.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Brand */
        $model = $this->brandBuilder->create();

        $res = $this->deleteJson(route('api.v1.inventories.brand.delete', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Brand */
        $model = $this->brandBuilder->create();

        $res = $this->deleteJson(route('api.v1.inventories.brand.delete', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
