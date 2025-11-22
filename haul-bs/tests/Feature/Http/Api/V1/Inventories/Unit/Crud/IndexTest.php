<?php

namespace Feature\Http\Api\V1\Inventories\Unit\Crud;

use App\Models\Inventories\Unit;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\Builders\Inventories\UnitBuilder;
use Tests\TestCase;

class IndexTest extends TestCase
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
    public function success_list()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $m_1 Unit */
        $m_1 = $this->unitBuilder->name('aaaaa')->create();
        $m_2 = $this->unitBuilder->name('zzzzz')->create();
        $m_3 = $this->unitBuilder->name('kkkkl')->create();

        $this->inventoryBuilder->unit($m_1)->create();

        $this->getJson(route('api.v1.inventories.unit'))
            ->assertJson([
                'data' => [
                    [
                        'id' => $m_1->id,
                        'name' => $m_1->name,
                        'accept_decimals' => $m_1->accept_decimals,
                        'hasRelatedEntities' => true,
                    ],
                    ['id' => $m_3->id],
                    ['id' => $m_2->id],
                ],
            ])
            ->assertJsonCount(3, 'data')
        ;
    }

    /** @test */
    public function success_empty()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.inventories.unit'))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $res = $this->getJson(route('api.v1.inventories.unit'));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.inventories.unit'));

        self::assertUnauthenticatedMessage($res);
    }
}
