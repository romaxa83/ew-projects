<?php

namespace Feature\Http\Api\V1\Inventories\Unit\Crud;

use App\Models\Inventories\Unit;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Inventories\UnitBuilder;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected UnitBuilder $unitBuilder;
    protected InventoryBuilder $inventoryBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->unitBuilder = resolve(UnitBuilder::class);
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
    }

    /** @test */
    public function success_delete()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Unit */
        $model = $this->unitBuilder->create();

        $id = $model->id;

        $this->deleteJson(route('api.v1.inventories.unit.delete', ['id' => $model->id]))
            ->assertNoContent()
        ;

        $this->assertFalse(Unit::query()->where('id', $id)->exists());
    }

    /** @test */
    public function fail_delete_has_related_entities()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Unit */
        $model = $this->unitBuilder->create();

        $this->inventoryBuilder->unit($model)->create();

        $id = $model->id;

        $res = $this->deleteJson(route('api.v1.inventories.unit.delete', ['id' => $model->id]))
        ;

        self::assertErrorMsg(
            $res,
            __("exceptions.inventories.unit.has_related_entities"),
            Response::HTTP_UNPROCESSABLE_ENTITY
        );

        $this->assertTrue(Unit::query()->where('id', $id)->exists());
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->deleteJson(route('api.v1.inventories.unit.delete', ['id' => 0]));

        self::assertErrorMsg($res, __("exceptions.inventories.unit.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Unit */
        $model = $this->unitBuilder->create();

        $res = $this->deleteJson(route('api.v1.inventories.unit.delete', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Unit */
        $model = $this->unitBuilder->create();

        $res = $this->deleteJson(route('api.v1.inventories.unit.delete', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
