<?php

namespace Tests\Feature\Orders\Order;

use App\Enums\Catalog\MoveSizeTypeEnum;
use App\Models\Division;
use App\Models\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\Builders\Users\RoleBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class AjaxInfoOrderTest extends TestCase
{
    use DatabaseTransactions;

    protected DivisionBuilder $divisionBuilder;
    protected UserBuilder $userBuilder;
    protected RoleBuilder $roleBuilder;
    protected OrderBuilder $orderBuilder;

    public function setUp(): void
    {
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->roleBuilder = resolve(RoleBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function check_types_as_move_size()
    {
        $role = $this->roleBuilder
            ->asPartner()
            ->create();
        $user = $this->userBuilder
            ->roles($role)
            ->create();
        $this->loginUser($user);

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data = [
            'order_id' => $model->id,
        ];

        $this->post(route('orders.record.info-statuses'), $data)
            ->assertJson([
                'success' => true,
                'dataSources' => [
                    'moveSizes' => MoveSizeTypeEnum::forSelect()
                ]
            ])
        ;
    }
}
