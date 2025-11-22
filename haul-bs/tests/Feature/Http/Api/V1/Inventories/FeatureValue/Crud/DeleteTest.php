<?php

namespace Feature\Http\Api\V1\Inventories\FeatureValue\Crud;

use App\Events\Events\Inventories\FeatureValues\DeleteFeatureValueEvent;
use App\Events\Listeners\Inventories\FeatureValues\SyncEComDeleteFeatureValueListener;
use App\Models\Inventories\Features\Value;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Inventories\FeatureBuilder;
use Tests\Builders\Inventories\FeatureValueBuilder;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Inventories\InventoryFeatureValueBuilder;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected InventoryBuilder $inventoryBuilder;
    protected FeatureBuilder $featureBuilder;
    protected FeatureValueBuilder $featureValueBuilder;
    protected InventoryFeatureValueBuilder $inventoryFeatureValueBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->featureBuilder = resolve(FeatureBuilder::class);
        $this->featureValueBuilder = resolve(FeatureValueBuilder::class);
        $this->inventoryFeatureValueBuilder = resolve(InventoryFeatureValueBuilder::class);
    }

    /** @test */
    public function success_delete()
    {
        Event::fake([DeleteFeatureValueEvent::class]);

        $this->loginUserAsSuperAdmin();

        /** @var $model Value */
        $model = $this->featureValueBuilder->create();

        $id = $model->id;

        $this->deleteJson(route('api.v1.inventories.feature.value.delete', ['id' => $model->id]))
            ->assertNoContent()
        ;

        $this->assertFalse(Value::query()->where('id', $id)->exists());

        Event::assertDispatched(fn (DeleteFeatureValueEvent $event) =>
            $event->getModel()->id === $id
        );
        Event::assertListening(
            DeleteFeatureValueEvent::class,
            SyncEComDeleteFeatureValueListener::class
        );
    }

    /** @test */
    public function fail_has_relation_inventories()
    {
        $this->loginUserAsSuperAdmin();

        $feature = $this->featureBuilder->create();

        /** @var $model Value */
        $model = $this->featureValueBuilder->feature($feature)->create();

        $inventory = $this->inventoryBuilder->create();

        $this->inventoryFeatureValueBuilder
            ->inventory($inventory)
            ->value($model)
            ->feature($feature)
            ->create()
        ;

        $res = $this->deleteJson(route('api.v1.inventories.feature.value.delete', ['id' => $model->id]))
        ;

        self::assertErrorMsg(
            $res,
            __("exceptions.inventories.features.value.has_inventory"),
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->deleteJson(route('api.v1.inventories.feature.value.delete', ['id' => 0]));

        self::assertErrorMsg($res, __("exceptions.inventories.features.value.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Value */
        $model = $this->featureValueBuilder->create();

        $res = $this->deleteJson(route('api.v1.inventories.feature.value.delete', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Value */
        $model = $this->featureValueBuilder->create();

        $res = $this->deleteJson(route('api.v1.inventories.feature.value.delete', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
