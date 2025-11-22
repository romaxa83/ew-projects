<?php


namespace Api\Logs;


use App\Models\History\History;
use App\Models\Orders\Order;
use App\Models\Orders\OrderSignature;
use App\Models\Users\User;
use App\Notifications\Orders\SendDocs;
use App\Services\Logs\DeliveryLogService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class EmailDeliveryLogsTest extends TestCase
{
    use DatabaseTransactions;
    use OrderFactoryHelper;
    use UserFactoryHelper;

    public function test_email_delivery_log(): void
    {
        $order = $this->orderFactory();
        $public_token = $order->public_token;

        $this->assertDatabaseCount(History::TABLE_NAME, 0);

        $this->postJson(
            route('email-delivery-logs'),
            [
                'logs' => [
                    [
                        "order_id" => $public_token,
                        "recipient_email" => "mail@server.com",
                        "type" => SendDocs::ATTACHMENT_BOL . ',' . SendDocs::ATTACHMENT_BROKER_INVOICE,
                        "result" => DeliveryLogService::EMAIL_RESULT_SUCCESS,
                        "env_type" => "",
                    ],
                ],
            ],
            [
                'Authorization' => config('emaillog.log_token'),
            ]
        )
            ->assertOk();

        $this->assertDatabaseCount(
            History::class,
            1
        );

        $this->assertDatabaseHas(
            History::class,
            [
                'model_type' => Order::class,
                'model_id' => $order->id,
                'user_id' => null,
                'user_role' => null,
                'message' => 'history.delivered_docs_success',
                'meta' => json_encode([
                    'documents' => ucfirst(SendDocs::ATTACHMENTS_NAME[SendDocs::ATTACHMENT_BOL]),
                    'document' => SendDocs::ATTACHMENTS_NAME[SendDocs::ATTACHMENT_BROKER_INVOICE],
                    'email' => 'mail@server.com'
                ])
            ]
        );
    }

    public function test_email_delivery_log_signature_link(): void
    {
        $user = $this->userFactory(User::ACCOUNTANT_ROLE);
        /**@var OrderSignature $order*/
        $order = $this->orderFactory();

        /**@var OrderSignature $signature*/
        $signature = OrderSignature::factory()->create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'inspection_location' => Order::LOCATION_PICKUP
        ]);

        $this->postJson(
            route('email-delivery-logs'),
            [
                'logs' => [
                    [
                        "order_id" => $signature->signature_token,
                        "recipient_email" => $signature->email,
                        "type" => DeliveryLogService::EMAIL_SIGNATURE_LINK_TYPE,
                        "result" => DeliveryLogService::EMAIL_RESULT_SUCCESS,
                        "env_type" => config('emaillog.env_type'),
                    ],
                ],
            ],
            [
                'Authorization' => config('emaillog.log_token'),
            ]
        )
            ->assertOk();

        $this->assertDatabaseHas(
            History::class,
            [
                'model_type' => Order::class,
                'model_id' => $order->id,
                'message' => 'history.delivered_signature_link_success',
                'meta' => json_encode([
                    'location' => $signature->inspection_location,
                    'email_recipient' => $signature->email
                ])
            ]
        );
    }

    public function test_email_fail_delivery_log_signature_link(): void
    {
        $user = $this->userFactory(User::ACCOUNTANT_ROLE);
        /**@var OrderSignature $order*/
        $order = $this->orderFactory();

        /**@var OrderSignature $signature*/
        $signature = OrderSignature::factory()->create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'inspection_location' => Order::LOCATION_PICKUP
        ]);

        $this->postJson(
            route('email-delivery-logs'),
            [
                'logs' => [
                    [
                        "order_id" => $signature->signature_token,
                        "recipient_email" => $signature->email,
                        "type" => DeliveryLogService::EMAIL_SIGNATURE_LINK_TYPE,
                        "result" => DeliveryLogService::EMAIL_RESULT_FAIL,
                        "env_type" => config('emaillog.env_type'),
                    ],
                ],
            ],
            [
                'Authorization' => config('emaillog.log_token'),
            ]
        )
            ->assertOk();

        $this->assertDatabaseHas(
            History::class,
            [
                'model_type' => Order::class,
                'model_id' => $order->id,
                'message' => 'history.delivered_signature_link_fail',
                'meta' => json_encode([
                    'location' => $signature->inspection_location,
                    'email_recipient' => $signature->email
                ])
            ]
        );
    }
}
