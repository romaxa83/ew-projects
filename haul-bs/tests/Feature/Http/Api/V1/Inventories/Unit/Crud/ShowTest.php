<?php

namespace Feature\Http\Api\V1\Inventories\Unit\Crud;

use App\Models\Inventories\Unit;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Inventories\UnitBuilder;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use DatabaseTransactions;

    protected UnitBuilder $unitBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->unitBuilder = resolve(UnitBuilder::class);
    }

    /** @test */
    public function success_show()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Unit */
        $model = $this->unitBuilder->create();

        $this->getJson(route('api.v1.inventories.unit.show', ['id' => $model->id]))
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'name' => $model->name,
                    'accept_decimals' => $model->accept_decimals,
                    'hasRelatedEntities' => false,
                ],
            ])
        ;
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->getJson(route('api.v1.inventories.unit.show', ['id' => 0]))
        ;

        self::assertErrorMsg($res, __("exceptions.inventories.unit.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Unit */
        $model = $this->unitBuilder->create();

        $res = $this->getJson(route('api.v1.inventories.unit.show', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Unit */
        $model = $this->unitBuilder->create();

        $res = $this->getJson(route('api.v1.inventories.unit.show', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
