<?php

namespace Tests\Unit\Services\Audit\TransformDataService\ForOrder;

use App\Models\Audit;
use App\Models\Order;
use App\Services\Audit\TransformDataService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Audits\AuditBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\Builders\Orders\PaymentBuilder;
use Tests\TestCase;

class ByOrderPaymentTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected PaymentBuilder $paymentBuilder;

    protected AuditBuilder $auditBuilder;

    protected TransformDataService $service;

    public function setUp(): void
    {
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->paymentBuilder = resolve(PaymentBuilder::class);
        $this->auditBuilder = resolve(AuditBuilder::class);

        $this->service = resolve(TransformDataService::class);


        parent::setUp();
    }

    /** @test */
    public function audit_create_payment()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $payment Order\Payment */
        $payment = $this->paymentBuilder
            ->order($model)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($payment)
            ->new_values([
                'id' => $payment->id,
                'user_id' => $payment->user_id,
                'amount' => 10,
                'order_id' => $model->id,
                'payment_account_id' => $payment->payment_account_id,
                'description' => 'some desc',
                'in_total_sum' => 1,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(4, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'amount');
        $this->assertEquals($res[0]['details'][0]['new'], '10');
        $this->assertNull($res[0]['details'][0]['old']);

        $this->assertEquals($res[0]['details'][1]['field'], 'method');
        $this->assertEquals($res[0]['details'][1]['new'], $payment->account->title);
        $this->assertNull($res[0]['details'][1]['old']);

        $this->assertEquals($res[0]['details'][2]['field'], 'description');
        $this->assertEquals($res[0]['details'][2]['new'], 'some desc');
        $this->assertNull($res[0]['details'][2]['old']);

        $this->assertEquals($res[0]['details'][3]['field'], 'in total');
        $this->assertEquals($res[0]['details'][3]['new'], 'true');
        $this->assertNull($res[0]['details'][3]['old']);

        $this->assertEquals($res[0]['action'], Audit::EVENT_CREATED);
        $this->assertEquals($res[0]['entity'], 'Payment');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_change_in_total_sum_to_false()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $payment Order\Payment */
        $payment = $this->paymentBuilder
            ->order($model)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($payment)
            ->new_values([
                'in_total_sum' => false,
            ])
            ->old_values([
                'in_total_sum' => 1,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'in total');
        $this->assertEquals($res[0]['details'][0]['new'], 'false');
        $this->assertEquals($res[0]['details'][0]['old'], 'true');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Payment');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }

    /** @test */
    public function audit_change_in_total_sum_to_true()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        /** @var $payment Order\Payment */
        $payment = $this->paymentBuilder
            ->order($model)->create();

        /** @var $audit Audit */
        $audit = $this->auditBuilder
            ->auditable($payment)
            ->new_values([
                'in_total_sum' => true,
            ])
            ->old_values([
                'in_total_sum' => 0,
            ])
            ->create();

        $res = $this->service
            ->forOrder($audit);

        $this->assertEquals(1, count($res));
        $this->assertEquals(1, count($res[0]['details']));

        $this->assertEquals($res[0]['details'][0]['field'], 'in total');
        $this->assertEquals($res[0]['details'][0]['old'], 'false');
        $this->assertEquals($res[0]['details'][0]['new'], 'true');

        $this->assertEquals($res[0]['action'], Audit::EVENT_UPDATED);
        $this->assertEquals($res[0]['entity'], 'Payment');
        $this->assertEquals($res[0]['created_at'], $audit->created_at->timestamp);
        $this->assertEquals($res[0]['user']->id, $audit->user_id);
        $this->assertFalse($res[0]['is_client_activity']);
        $this->assertNull($res[0]['client']);
    }
}
