<?php

namespace Tests\Feature\Http\Api\V1\TypeOfWorks\Crud;

use App\Models\TypeOfWorks\TypeOfWork;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\TypeOfWorks\TypeOfWorkBuilder;
use Tests\Builders\TypeOfWorks\TypeOfWorkInventoryBuilder;
use Tests\TestCase;

class ShowTest extends TestCase
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
    public function success_show()
    {
        $this->loginUserAsSuperAdmin();

        $i_1 = $this->inventoryBuilder->create();
        $i_2 = $this->inventoryBuilder->create();

        /** @var $m TypeOfWork */
        $m = $this->typeOfWorkBuilder->create();

        $this->typeOfWorkInventoryBuilder->work($m)->inventory($i_1)->create();
        $this->typeOfWorkInventoryBuilder->work($m)->inventory($i_2)->create();

        $this->getJson(route('api.v1.type-of-works.show', ['id' => $m->id]))
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'duration',
                    'hourly_rate',
                    'inventories' => [
                        [
                            'id',
                            'inventory_id',
                            'name',
                            'stock_number',
                            'price',
                            'quantity',
                            'unit' => [
                                'id',
                                'name',
                                'accept_decimals',
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    'id' => $m->id,
                ],
            ])
            ->assertJsonCount(2, 'data.inventories')
        ;
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->getJson(route('api.v1.type-of-works.show', ['id' => 0]));

        self::assertErrorMsg($res, __("exceptions.type_of_works.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $m TypeOfWork */
        $m = $this->typeOfWorkBuilder->create();

        $res = $this->getJson(route('api.v1.type-of-works.show', ['id' => $m->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $m TypeOfWork */
        $m = $this->typeOfWorkBuilder->create();

        $res = $this->getJson(route('api.v1.type-of-works.show', ['id' => $m->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
