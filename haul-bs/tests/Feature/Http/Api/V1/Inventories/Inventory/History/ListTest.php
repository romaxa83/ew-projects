<?php

namespace Feature\Http\Api\V1\Inventories\Inventory\History;

use App\Models\Inventories\Inventory;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\History\HistoryBuilder;
use Tests\Builders\Inventories\InventoryBuilder;
use Tests\TestCase;

class ListTest extends TestCase
{
    use DatabaseTransactions;

    protected InventoryBuilder $inventoryBuilder;
    protected HistoryBuilder $historyBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->historyBuilder = resolve(HistoryBuilder::class);
    }

    /** @test */
    public function success_list()
    {
        $user = $this->loginUserAsSuperAdmin();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $msg = 'history.inventory.created';
        $msg_attr = [
            'role' => $user->role_name_pretty,
            'full_name' => $user->full_name,
            'email' => $user->email->getValue(),
            'stock_number' => $model->stock_number,
            'user_id' => $user->id,
        ];

        $h_1 = $this->historyBuilder->model($model)
            ->msg($msg)
            ->msg_attr($msg_attr)
            ->performed_at($date->subHours(2))
            ->create();
        $h_2 = $this->historyBuilder->model($model)->performed_at($date->subHours(1))->create();

        $this->getJson(route('api.v1.inventories.list-history', ['id' => $model->id]))
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'model_id',
                        'user_id',
                        'message',
                        'meta',
                        'performed_at',
                        'performed_timezone',
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    ['id' => $h_2->id,],
                    [
                        'id' => $h_1->id,
                        'message' => __($msg, $msg_attr)
                    ],
                ],
            ])
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function success_empty()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $this->getJson(route('api.v1.inventories.list-history', ['id' => $model->id]))
            ->assertJsonCount(0, 'data')
        ;
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->getJson(route('api.v1.inventories.list-history', ['id' => 0]))
        ;

        self::assertErrorMsg($res, __("exceptions.inventories.inventory.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $res = $this->getJson(route('api.v1.inventories.list-history', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Inventory */
        $model = $this->inventoryBuilder->create();

        $res = $this->getJson(route('api.v1.inventories.list-history', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
