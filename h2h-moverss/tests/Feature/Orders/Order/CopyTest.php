<?php

namespace Tests\Feature\Orders\Order;

use App\Models\Audit;
use App\Models\Division;
use App\Models\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class CopyTest extends TestCase
{
    use DatabaseTransactions;

    protected DivisionBuilder $divisionBuilder;
    protected UserBuilder $userBuilder;
    protected OrderBuilder $orderBuilder;

    public function setUp(): void
    {
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function copy_check_audit()
    {
        $user = $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $model Order */
        $model = $this->orderBuilder->create();
        $model->audits()->delete();

        $data = [
            'order_id' => $model->id,
        ];

        $this->assertNull(Order::where('base_id', $model->id)->first());

        $this->post(route('orders.record.copy', [
            'id' => $model->id,
        ]), $data)
            ->assertJsonStructure([
                'success',
                'href',
                'msg',
            ])
        ;

        $copy = Order::where('base_id', $model->id)->first();

        $this->assertEquals(count($copy->audits), 2);

        $fAudit = $copy->audits->sortBy('created_at')->values()[0];
        $sAudit = $copy->audits->sortBy('created_at')->values()[1];

        $this->assertEquals($fAudit->event, Audit::EVENT_CLONED);
        $this->assertEquals($fAudit->order_id, $copy->id);
        $this->assertEmpty($fAudit->new_values);
        $this->assertEquals($fAudit->old_values, ['order_id' => $model->id]);

        $this->assertEquals($sAudit->event, Audit::EVENT_CREATED);
        $this->assertEquals($sAudit->order_id, $copy->id);

        $model->refresh();

        $this->assertEquals(count($model->audits), 1);

        $this->assertEquals($model->audits[0]->event, Audit::EVENT_CLONED);
        $this->assertEmpty($model->audits[0]->old_values);
        $this->assertEquals($model->audits[0]->new_values, ['order_id' => $copy->id]);
    }
}
