<?php

namespace Feature\Http\Api\V1\Inventories\Feature\Crud;

use App\Events\Events\Inventories\Features\DeleteFeatureEvent;
use App\Events\Listeners\Inventories\Features\SyncEComDeleteFeatureListener;
use App\Models\Inventories\Features\Feature;
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
        Event::fake([DeleteFeatureEvent::class]);

        $this->loginUserAsSuperAdmin();

        /** @var $model Feature */
        $model = $this->featureBuilder->create();

        $value = $this->featureValueBuilder->feature($model)->create();

        $id = $model->id;
        $valueId = $value->id;

        $this->deleteJson(route('api.v1.inventories.feature.delete', ['id' => $model->id]))
            ->assertNoContent()
        ;

        $this->assertFalse(Feature::query()->where('id', $id)->exists());
        $this->assertFalse(Value::query()->where('id', $valueId)->exists());

        Event::assertDispatched(fn (DeleteFeatureEvent $event) =>
            $event->getModel()->id === (int)$id
        );
        Event::assertListening(
            DeleteFeatureEvent::class,
            SyncEComDeleteFeatureListener::class
        );
    }

    /** @test */
    public function fail_has_relation_inventories()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Feature */
        $model = $this->featureBuilder->create();

        $value = $this->featureValueBuilder->feature($model)->create();
        $inventory = $this->inventoryBuilder->create();

        $this->inventoryFeatureValueBuilder
            ->inventory($inventory)
            ->value($value)
            ->feature($model)
            ->create()
        ;

        $res = $this->deleteJson(route('api.v1.inventories.feature.delete', ['id' => $model->id]))
        ;

        self::assertErrorMsg(
            $res,
            __("exceptions.inventories.features.feature.has_inventory"),
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->deleteJson(route('api.v1.inventories.feature.delete', ['id' => 0]));

        self::assertErrorMsg($res, __("exceptions.inventories.features.feature.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Feature */
        $model = $this->featureBuilder->create();

        $res = $this->deleteJson(route('api.v1.inventories.feature.delete', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Feature */
        $model = $this->featureBuilder->create();

        $res = $this->deleteJson(route('api.v1.inventories.feature.delete', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
