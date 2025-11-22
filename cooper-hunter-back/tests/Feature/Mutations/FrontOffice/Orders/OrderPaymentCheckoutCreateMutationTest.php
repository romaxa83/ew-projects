<?php

namespace Tests\Feature\Mutations\FrontOffice\Orders;

use App\Enums\Payments\PaymentReturnPlatformEnum;
use App\Enums\Payments\PayPalCheckoutStatusEnum;
use App\GraphQL\Mutations\FrontOffice\Orders\OrderPaymentCheckoutCreateMutation;
use App\Models\Payments\PayPalCheckout;
use App\Services\Payment\PayPalService;
use Carbon\Carbon;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Mockery\MockInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Models\ProjectCreateTrait;

class OrderPaymentCheckoutCreateMutationTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;
    use ProjectCreateTrait;
    use OrderCreateTrait;

    public function test_create_checkout_url(): void
    {
        $member = $this->loginAsTechnicianWithRole();

        $order = $this->setOrderTechnician($member)
            ->createPendingPaidOrder();

        $url = $this->faker->url;
        $checkoutId = $this->faker->lexify;

        $this->mock(
            Client::class,
            fn(MockInterface $mock) => $mock->shouldReceive('request')
                ->times(2)
                ->andReturns(
                    new Response(
                        body: json_encode(
                            [
                                'access_token' => $this->faker->lexify,
                                'expires_in' => 3600
                            ]
                        )
                    ),
                    new Response(
                        body: json_encode(
                            [
                                'id' => $checkoutId,
                                'status' => PayPalCheckoutStatusEnum::CREATED,
                                'links' => [
                                    [
                                        'href' => $url,
                                        'rel' => 'approve'
                                    ]
                                ],
                                'create_time' => Carbon::now()
                                    ->toDateTimeString()
                            ]
                        )
                    )
                )
        )
            ->makePartial();

        $query = new GraphQLQuery(
            OrderPaymentCheckoutCreateMutation::NAME,
            [
                'id' => $order->id,
                'platform' => new EnumValue(PaymentReturnPlatformEnum::WEB)
            ],
            [
                'url'
            ]
        );

        $this->postGraphQL($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderPaymentCheckoutCreateMutation::NAME => [
                            'url' => $url
                        ]
                    ]
                ]
            );

        $this->assertDatabaseHas(
            PayPalCheckout::class,
            [
                'id' => $checkoutId,
                'order_id' => $order->id,
                'checkout_status' => PayPalCheckoutStatusEnum::CREATED,
            ]
        );
    }

    public function test_create_checkout_url_with_token_from_cache(): void
    {
        $member = $this->loginAsTechnicianWithRole();

        $order = $this->setOrderTechnician($member)
            ->createPendingPaidOrder();

        $url = $this->faker->url;

        Cache::shouldReceive('get')
            ->once()
            ->with(PayPalService::TOKEN_CACHE_KEY)
            ->andReturn($this->faker->lexify);

        $this->mock(
            Client::class,
            fn(MockInterface $mock) => $mock->shouldReceive('request')
                ->once()
                ->andReturns(
                    new Response(
                        body: json_encode(
                            [
                                'id' => $this->faker->lexify,
                                'status' => PayPalCheckoutStatusEnum::CREATED,
                                'links' => [
                                    [
                                        'href' => $url,
                                        'rel' => 'approve'
                                    ]
                                ],
                                'create_time' => Carbon::now()
                                    ->toDateTimeString()
                            ]
                        )
                    )
                )
        )
            ->makePartial();

        $query = new GraphQLQuery(
            OrderPaymentCheckoutCreateMutation::NAME,
            [
                'id' => $order->id,
                'platform' => new EnumValue(PaymentReturnPlatformEnum::WEB)
            ],
            [
                'url'
            ]
        );

        $this->postGraphQL($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderPaymentCheckoutCreateMutation::NAME => [
                            'url' => $url
                        ]
                    ]
                ]
            );
    }

    public function test_create_checkout_url_with_invalid_token_in_cache(): void
    {
        $member = $this->loginAsTechnicianWithRole();

        $order = $this->setOrderTechnician($member)
            ->createPendingPaidOrder();

        $url = $this->faker->url;
        $token = $this->faker->lexify;

        Cache::shouldReceive('get')
            ->once()
            ->with(PayPalService::TOKEN_CACHE_KEY)
            ->andReturn($this->faker->lexify);

        Cache::shouldReceive('put')
            ->once()
            ->withSomeOfArgs(PayPalService::TOKEN_CACHE_KEY, $token)
            ->andReturn(true);

        $exception = $this->mock(
            ClientException::class,
            fn(MockInterface $mock) => $mock->shouldReceive('getResponse')
                ->once()
                ->andReturn(
                    $this->mock(
                        ResponseInterface::class,
                        fn(MockInterface $mock_i) => $mock_i->shouldReceive('getBody')
                            ->once()
                            ->andReturn(
                                $this->mock(
                                    StreamInterface::class,
                                    fn(MockInterface $mock_s) => $mock_s->shouldReceive('getContents')
                                        ->once()
                                        ->andReturn(
                                            json_encode(
                                                [
                                                    'error' => PayPalService::TOKEN_INVALID_ERROR
                                                ]
                                            )
                                        )
                                )
                                    ->makePartial()

                            )
                    )
                        ->makePartial()
                )
        )
            ->makePartial();

        $this->mock(
            Client::class,
            function (MockInterface $mock) use ($exception, $url, $token)
            {
                $mock->shouldReceive('request')
                    ->once()
                    ->andThrows($exception);

                $mock->shouldReceive('request')
                    ->times(2)
                    ->andReturns(
                        new Response(
                            body: json_encode(
                                [
                                    'access_token' => $token,
                                    'expires_in' => 3600
                                ]
                            )
                        ),
                        new Response(
                            body: json_encode(
                                [
                                    'id' => $this->faker->lexify,
                                    'status' => PayPalCheckoutStatusEnum::CREATED,
                                    'links' => [
                                        [
                                            'href' => $url,
                                            'rel' => 'approve'
                                        ]
                                    ],
                                    'create_time' => Carbon::now()
                                        ->toDateTimeString()
                                ]
                            )
                        )
                    );
            }
        )
            ->makePartial();

        $query = new GraphQLQuery(
            OrderPaymentCheckoutCreateMutation::NAME,
            [
                'id' => $order->id,
                'platform' => new EnumValue(PaymentReturnPlatformEnum::WEB)
            ],
            [
                'url'
            ]
        );

        $this->postGraphQL($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderPaymentCheckoutCreateMutation::NAME => [
                            'url' => $url
                        ]
                    ]
                ]
            );
    }

    public function test_get_checkout_url_from_db(): void
    {
        $member = $this->loginAsTechnicianWithRole();

        $order = $this->setOrderTechnician($member)
            ->createPendingPaidOrder();

        $checkout = PayPalCheckout::factory()
            ->create(
                [
                    'order_id' => $order->id,
                    'amount' => $order->payment->order_price_with_discount
                ]
            );

        Cache::shouldReceive('get')
            ->never();

        Cache::shouldReceive('put')
            ->never();

        $this->mock(
            Client::class,
            fn(MockInterface $mock) => $mock->shouldReceive('request')
                ->never()
        );

        $query = new GraphQLQuery(
            OrderPaymentCheckoutCreateMutation::NAME,
            [
                'id' => $order->id,
                'platform' => new EnumValue(PaymentReturnPlatformEnum::WEB)
            ],
            [
                'url'
            ]
        );

        $this->postGraphQL($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderPaymentCheckoutCreateMutation::NAME => [
                            'url' => $checkout->approve_url
                        ]
                    ]
                ]
            );
    }
}
