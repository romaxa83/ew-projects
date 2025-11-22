<?php

namespace Feature\Http\Api\V1\Inventories\Inventory\Crud;

use App\Enums\Orders\BS\OrderStatus;
use App\Events\Events\Inventories\Inventories\DeleteInventoryEvent;
use App\Events\Listeners\Inventories\Inventories\SyncEComDeleteInventoryListener;
use App\Foundations\Modules\Seo\Models\Seo;
use App\Models\Inventories\Brand;
use App\Models\Inventories\Inventory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Orders\BS\OrderBuilder;
use Tests\Builders\Orders\BS\OrderTypeOfWorkBuilder;
use Tests\Builders\Orders\BS\OrderTypeOfWorkInventoryBuilder;
use Tests\Builders\Seo\SeoBuilder;
use Tests\Builders\TypeOfWorks\TypeOfWorkBuilder;
use Tests\Builders\TypeOfWorks\TypeOfWorkInventoryBuilder;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;
    protected SeoBuilder $seoBuilder;
    protected InventoryBuilder $inventoryBuilder;
    protected OrderBuilder $orderBuilder;
    protected OrderTypeOfWorkBuilder $orderTypeOfWorkBuilder;
    protected OrderTypeOfWorkInventoryBuilder $orderTypeOfWorkInventoryBuilder;

    protected TypeOfWorkBuilder $typeOfWorkBuilder;
    protected TypeOfWorkInventoryBuilder $typeOfWorkInventoryBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->seoBuilder = resolve(SeoBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->orderTypeOfWorkBuilder = resolve(OrderTypeOfWorkBuilder::class);
        $this->orderTypeOfWorkInventoryBuilder = resolve(OrderTypeOfWorkInventoryBuilder::class);
        $this->typeOfWorkBuilder = resolve(TypeOfWorkBuilder::class);
        $this->typeOfWorkInventoryBuilder = resolve(TypeOfWorkInventoryBuilder::class);
    }

    /** @test */
    public function success_delete()
    {
        Event::fake([DeleteInventoryEvent::class]);

        $this->loginUserAsSuperAdmin();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->quantity(0)->create();

        $this->seoBuilder->model($model)->create();

        $id = $model->id;

        $this->deleteJson(route('api.v1.inventories.delete', ['id' => $model->id]))
            ->assertNoContent()
        ;

        $this->assertFalse(Inventory::query()->where('id', $id)->exists());
        $this->assertFalse(Seo::query()->where('model_id', $id)->where('model_type', Brand::MORPH_NAME)->exists());

        Event::assertDispatched(fn (DeleteInventoryEvent $event) =>
            $event->getModel()->id === $id
        );
        Event::assertListening(
            DeleteInventoryEvent::class,
            SyncEComDeleteInventoryListener::class
        );
    }

    /** @test */
    public function fail_delete_in_stock()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->quantity(1)->create();

        $res = $this->deleteJson(route('api.v1.inventories.delete', ['id' => $model->id]))
        ;

        self::assertErrorMsg($res, __("exceptions.inventories.inventory.cant_deleted_in_stock"), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_delete_has_open_order()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->quantity(0)->create();

        $order = $this->orderBuilder->status(OrderStatus::New->value)->create();
        $work = $this->orderTypeOfWorkBuilder->order($order)->create();
        $this->orderTypeOfWorkInventoryBuilder->inventory($model)->type_of_work($work)->create();

        $res = $this->deleteJson(route('api.v1.inventories.delete', ['id' => $model->id]))
        ;

        $url = str_replace('{id}', $model->id, config('routes.front.bs_open_orders_with_inventory_filter_url'));
        $link = '<a href="' . $url . '">' . trans('open orders') . '</a>';
        $msg = trans('This part is used in ') . $link . '. Please delete order permanently first.';

        self::assertErrorMsg($res, $msg, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_delete_has_deleted_order()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->quantity(0)->create();

        $order = $this->orderBuilder->status(OrderStatus::Deleted->value)->deleted()->create();
        $work = $this->orderTypeOfWorkBuilder->order($order)->create();
        $this->orderTypeOfWorkInventoryBuilder->inventory($model)->type_of_work($work)->create();

        $res = $this->deleteJson(route('api.v1.inventories.delete', ['id' => $model->id]))
        ;

        $url = str_replace('{id}', $model->id, config('routes.front.bs_deleted_orders_with_inventory_filter_url'));
        $link = '<a href="' . $url . '">' . trans('deleted orders') . '</a>';
        $msg = trans('This part is used in ') . $link . '. Please delete order permanently first.';

        self::assertErrorMsg($res, $msg, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_delete_has_type_of_work()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->quantity(0)->create();

        $work = $this->typeOfWorkBuilder->create();
        $this->typeOfWorkInventoryBuilder->inventory($model)->work($work)->create();

        $res = $this->deleteJson(route('api.v1.inventories.delete', ['id' => $model->id]))
        ;

        $url = str_replace('{id}', $model->id, config('routes.front.bs_types_of_work_with_inventory_filter_url'));
        $link = '<a href="' . $url . '">' . trans('types of work') . '</a>';
        $msg = trans('This part is used in ') . $link . '.';

        self::assertErrorMsg($res, $msg, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_delete_has_type_of_work_and_open_orders()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->quantity(0)->create();

        $order = $this->orderBuilder->status(OrderStatus::New->value)->create();
        $work = $this->orderTypeOfWorkBuilder->order($order)->create();
        $this->orderTypeOfWorkInventoryBuilder->inventory($model)->type_of_work($work)->create();

        $work_1 = $this->typeOfWorkBuilder->create();
        $this->typeOfWorkInventoryBuilder->inventory($model)->work($work_1)->create();

        $res = $this->deleteJson(route('api.v1.inventories.delete', ['id' => $model->id]))
        ;

        $url_order = str_replace('{id}', $model->id, config('routes.front.bs_open_orders_with_inventory_filter_url'));
        $link_order = '<a href="' . $url_order . '">' . trans('open orders') . '</a>';

        $url_work = str_replace('{id}', $model->id, config('routes.front.bs_types_of_work_with_inventory_filter_url'));
        $link_work = '<a href="' . $url_work . '">' . trans('types of work') . '</a>';

        $msg = trans('This part is used in ') . $link_order . ' and ' . $link_work . '. Please delete order permanently first.';

        self::assertErrorMsg($res, $msg, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->deleteJson(route('api.v1.inventories.delete', ['id' => 0]));

        self::assertErrorMsg($res, __("exceptions.inventories.inventory.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $res = $this->deleteJson(route('api.v1.inventories.delete', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $res = $this->deleteJson(route('api.v1.inventories.delete', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
