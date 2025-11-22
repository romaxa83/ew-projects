<?php

namespace Feature\Http\Api\V1\Orders\BS\Invoice;

use App\Foundations\Modules\History\Enums\HistoryType;
use App\Models\Orders\BS\Order;
use App\Notifications\Orders\BS\SendDocs;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\Builders\Orders\BS\OrderBuilder;
use Tests\Builders\Settings\SettingBuilder;
use Tests\TestCase;

class SendDocsTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected SettingBuilder $settingBuilder;

    protected array $data = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->settingBuilder = resolve(SettingBuilder::class);

        $this->data = [
            'recipient_email' => [
                'test@mail.net'
            ],
            'invoice_date' => '12/10/2020',
            'content' => [
                'invoice'
            ],
        ];
    }

    /** @test */
    public function success_send()
    {
        Notification::fake();

        $user = $this->loginUserAsSuperAdmin();

        $this->settingBuilder->create();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $this->assertFalse($model->is_billed);
        $this->assertNull($model->billed_at);

        $data = $this->data;

        $this->postJson(route('api.v1.orders.bs.send-docs', ['id' => $model->id]), $data)
            ->assertOk()
            ->assertJsonPath('data.message', 'Success')
        ;

        Notification::assertSentTo(new AnonymousNotifiable(), SendDocs::class);

        $model->refresh();
        $this->assertTrue($model->is_billed);
        $this->assertNotNull($model->billed_at);

        /** @var $model Order */
        $history = $model->histories[0];

        $this->assertEquals($history->type, HistoryType::ACTIVITY);
        $this->assertEquals($history->user_id, $user->id);
        $this->assertEquals($history->msg, 'history.order.common.send_docs');
        $this->assertEquals($history->msg_attr, [
            'role' => $user->role_name_pretty,
            'email' => $user->email->getValue(),
            'full_name' => $user->full_name,
            'user_id' => $user->id,
            'order_number' => $model->order_number,
            'order_id' => $model->id,
            "receivers" => $data['recipient_email'][0]
        ]);
        $this->assertEmpty($history->details);
    }

    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.orders.bs.send-docs', ['id' => 99999999]), $data)
        ;

        self::assertErrorMsg($res, __("exceptions.orders.bs.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.orders.bs.send-docs', ['id' => $model->id]), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data = $this->data;

        $res = $this->postJson(route('api.v1.orders.bs.send-docs', ['id' => $model->id]), $data);

        self::assertUnauthenticatedMessage($res);
    }
}
