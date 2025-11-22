<?php

namespace Tests\Feature\Http\Api\V1\TypeOfWorks\Crud;

use App\Models\TypeOfWorks\TypeOfWork;
use App\Models\TypeOfWorks\TypeOfWorkInventory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\TypeOfWorks\TypeOfWorkBuilder;
use Tests\Builders\TypeOfWorks\TypeOfWorkInventoryBuilder;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected InventoryBuilder $inventoryBuilder;
    protected TypeOfWorkBuilder $typeOfWorkBuilder;
    protected TypeOfWorkInventoryBuilder $typeOfWorkInventoryBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->typeOfWorkBuilder = resolve(TypeOfWorkBuilder::class);
        $this->typeOfWorkInventoryBuilder = resolve(TypeOfWorkInventoryBuilder::class);
    }

    /** @test */
    public function success_delete()
    {
        $this->loginUserAsSuperAdmin();

        $i_1 = $this->inventoryBuilder->create();

        /** @var $model TypeOfWork */
        $model = $this->typeOfWorkBuilder->create();

        $t_i = $this->typeOfWorkInventoryBuilder->work($model)->inventory($i_1)->create();

        $id = $model->id;
        $t_i_id = $t_i->id;

        $this->deleteJson(route('api.v1.type-of-works.delete', ['id' => $model->id]))
            ->assertNoContent()
        ;

        $this->assertFalse(TypeOfWork::query()->where('id', $id)->exists());
        $this->assertFalse(TypeOfWorkInventory::query()->where('id', $t_i_id)->exists());
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->deleteJson(route('api.v1.type-of-works.delete', ['id' => 0]));

        self::assertErrorMsg($res, __("exceptions.type_of_works.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model TypeOfWork */
        $model = $this->typeOfWorkBuilder->create();

        $res = $this->deleteJson(route('api.v1.type-of-works.delete', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model TypeOfWork */
        $model = $this->typeOfWorkBuilder->create();

        $res = $this->deleteJson(route('api.v1.type-of-works.delete', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
