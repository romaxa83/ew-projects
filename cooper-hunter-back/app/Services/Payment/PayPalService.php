<?php

namespace App\Services\Payment;

use App\Dto\Payments\PayPalWebhookSignatureDto;
use App\Enums\Payments\PayPalCheckoutStatusEnum;
use App\Enums\Payments\PayPalRefundStatusEnum;
use App\Exceptions\Payment\PayPalOrderApprovedException;
use App\Exceptions\Payment\PayPalSomethingWentWrongException;
use App\Models\Orders\Order;
use App\Models\Payments\PayPalCheckout;
use App\Services\Locations\StateService;
use App\Services\Orders\OrderService;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class PayPalService
{
    public const TOKEN_CACHE_KEY = 'paypal_access_token';
    public const TOKEN_INVALID_ERROR = 'invalid_token';

    private const METHOD_POST = 'POST';
    private const METHOD_GET = 'GET';
    private const METHOD_PATCH = 'PATCH';

    public const W_EVENT_ORDER_APPROVED = 'CHECKOUT.ORDER.APPROVED';
    public const W_EVENT_CAPTURE_COMPLETED = 'PAYMENT.CAPTURE.COMPLETED';
    public const W_EVENT_CAPTURE_REFUND = 'PAYMENT.CAPTURE.REFUNDED';

    public const W_EVENTS = [
        self::W_EVENT_CAPTURE_COMPLETED,
        self::W_EVENT_ORDER_APPROVED,
        self::W_EVENT_CAPTURE_REFUND,
    ];

    private ?string $accessToken = null;

    public function __construct(
        private Client $cURL,
        private StateService $stateService,
    ) {
    }

    /**
     * @param Order $order
     * @param string $platform
     * @return string
     * @throws PayPalSomethingWentWrongException
     * @throws PayPalOrderApprovedException
     */
    public function getApproveUrl(Order $order, string $platform): string
    {
        $this->checkApproveOrder($order);

        $checkout = $order->checkouts()
            ->platform($platform)
            ->where('checkout_status', PayPalCheckoutStatusEnum::CREATED)
            ->where(
                'created_at',
                '>=',
                Carbon::now('UTC')
                    ->subHours(config('paypal.setting.checkout_lifetime'))
                    ->getTimestamp()
            )
            ->first();

        if ($checkout) {
            return $checkout->approve_url;
        }

        $order->checkouts()
            ->platform($platform)
            ->delete();

        return $this->setAccessToken()
            ->createCheckoutOrder($order, $platform)->approve_url;
    }

    /**
     * @param Order $order
     * @throws PayPalOrderApprovedException
     */
    private function checkApproveOrder(Order $order): void
    {
        $checkouts = $order->checkouts()
            ->whereNotIn('checkout_status', PayPalCheckoutStatusEnum::canCreate())
            ->where(
                fn(Builder $builder) => $builder->whereNull('refund_status')
                    ->orWhere('refund_status', '<>', PayPalRefundStatusEnum::COMPLETED)
            );

        if (!$checkouts->exists()) {
            return;
        }

        throw new PayPalOrderApprovedException();
    }

    /**
     * @param Order $order
     * @param string $platform
     * @return PayPalCheckout
     * @throws PayPalSomethingWentWrongException
     */
    private function createCheckoutOrder(Order $order, string $platform): PayPalCheckout
    {
        $json = [
            'intent' => 'CAPTURE',
            'payer' => $this->getPayerDetails($order),
            'purchase_units' => [
                $this->makePurchaseUnit($order)
            ],
            'application_context' => array_merge(
                config('paypal.setting.application_context'),
                [
                    'return_url' => __config('paypal.urls.' . $platform . '.return', ['id' => $order->id]),
                    'cancel_url' => __config('paypal.urls.' . $platform . '.cancel', ['id' => $order->id]),
                ]
            )
        ];

        if (is_null($json['payer'])) {
            unset($json['payer']);
        }

        $response = $this->sendRequest(
            self::METHOD_POST,
            config('paypal.urls.methods.checkout'),
            [
                'json' => $json,
            ]
        );

        if (empty($response['id']) || empty($response['status']) || empty($response['links'])) {
            throw new PayPalSomethingWentWrongException();
        }

        $links = array_column($response['links'], 'href', 'rel');

        if (empty($links['approve'])) {
            throw new PayPalSomethingWentWrongException();
        }

        $checkout = new PayPalCheckout();

        $checkout->id = $response['id'];
        $checkout->checkout_status = $response['status'];
        $checkout->order_id = $order->id;
        $checkout->amount = $order->payment->order_price_with_discount;
        $checkout->return_platform = $platform;
        $checkout->approve_url = $links['approve'];
        $checkout->created_at = Carbon::parse($response['create_time'])
            ->getTimestamp();

        $checkout->save();

        return $checkout;
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $options
     * @return array
     * @throws PayPalSomethingWentWrongException
     */
    private function sendRequest(string $method, string $url, array $options = []): array
    {
        $options['base_uri'] = config('paypal.urls.api.' . config('paypal.mode'));

        $options['headers']['Accept'] = 'application/json';

        if ($this->accessToken !== null) {
            $options['headers'] = array_merge(
                $options['headers'],
                [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json',
                    'Prefer' => 'return=representation'
                ]
            );
        }

        Log::info('PAYPAL. Request data', $options);

        try {
            $response = $this->getJsonResponse(
                $this->cURL->request($method, $url, $options)
            );

            Log::info('PAYPAL. Response data', $response);

            return $response;
        } catch (GuzzleException|ClientException|ServerException $e) {
            try {
                $response = $this->getJsonResponse($e->getResponse());

                if (!empty($response['error']) && $response['error'] === self::TOKEN_INVALID_ERROR) {
                    return $this->setAccessToken(false)
                        ->sendRequest($method, $url, $options);
                }
            } catch (PayPalSomethingWentWrongException) {
                $response = [
                    'content' => $e->getResponse()
                        ->getBody()
                        ->getContents()
                ];
            }

            Log::error('PAYPAL. ' . $e->getMessage(), $response);

            throw new PayPalSomethingWentWrongException();
        }
    }

    /**
     * @param ResponseInterface $response
     * @return array
     * @throws PayPalSomethingWentWrongException
     */
    private function getJsonResponse(ResponseInterface $response): array
    {
        try {
            return jsonToArray(
                $response->getBody()
                    ->getContents()
            );
        } catch (JsonException) {
            throw new PayPalSomethingWentWrongException();
        }
    }

    /**
     * @param bool $checkInCache
     * @return $this
     */
    public function setAccessToken(bool $checkInCache = true): PayPalService
    {
        if ($checkInCache) {
            if ($this->accessToken !== null || ($this->accessToken = Cache::get(self::TOKEN_CACHE_KEY)) !== null) {
                return $this;
            }
        }

        $this->accessToken = null;

        [$this->accessToken, $expiresIn] = $this->getAccessToken();

        Cache::put(
            key: self::TOKEN_CACHE_KEY,
            value: $this->accessToken,
            ttl: Carbon::now()
                ->addSeconds($expiresIn)
                ->subMinutes(5)
                ->getTimestamp()
        );

        return $this;
    }

    /**
     * @return array
     * @throws PayPalSomethingWentWrongException
     */
    private function getAccessToken(): array
    {
        $response = $this->sendRequest(
            self::METHOD_POST,
            config('paypal.urls.methods.auth'),
            [
                'auth' => [
                    config('paypal.client_id'),
                    config('paypal.client_secret'),
                ],
                'form_params' => [
                    'grant_type' => 'client_credentials'
                ]
            ]
        );

        return [
            $response['access_token'],
            $response['expires_in'],
        ];
    }

    public function getPayerDetails(Order $order): ?array
    {
        try {
//            if (!$state = $this->stateService->getShortNameByState($order->shipping->state)) {
//                return null;
//            }

            $payer = [
                'name' => [
                    'given_name' => $order->shipping->first_name,
                    'surname' => $order->shipping->last_name,
                ],
                'address' => [
                    'address_line_1' => $order->shipping->address_first_line,
                    'address_line_2' => $order->shipping->address_second_line,
                    'admin_area_2' => $order->shipping->city,
                    'admin_area_1' => $order->shipping->state()->first()->short_name,
                    'postal_code' => $order->shipping->zip,
                    'country_code' => $order->shipping->country()->first()->country_code,
                ],
            ];

            if ($email = $order->technician?->email) {
                $payer['email_address'] = (string)$email;
            }

            if ($phone = $order->technician?->phone) {
                $payer['phone'] = [
                    'phone_type' => 'MOBILE',
                    'phone_number' => [
                        'national_number' => (string)$phone,
                    ],
                ];
            }

            return $payer;
        } catch (Throwable $e) {
            logger($e);
        }

        return null;
    }

    protected function makePurchaseUnit(Order $order): array
    {
        $data = [
            'reference_id' => (string)$order->id,
            'amount' => [
                'currency_code' => config('paypal.setting.currency_code'),
                'value' => (string)$order->payment->order_price_with_discount,
            ],
        ];

        if ($shipping = $this->getShippingAddress($order)) {
            $data = array_merge($data, compact('shipping'));
        }

        return $data;
    }

    public function getShippingAddress(Order $order): ?array
    {
//        if (!$stateShortName = $this->stateService->getShortNameByState($order->shipping->state)) {
//            return null;
//        }

        $shipping = [
            'name' => [
                'full_name' => $order->shipping->getFullName(),
            ],
            'address' => [
                'address_line_1' => $order->shipping->address_first_line,
                'admin_area_2' => $order->shipping->city,
                'admin_area_1' => $order->shipping->state()->first()->short_name,
                'postal_code' => $order->shipping->zip,
                'country_code' => $order->shipping->country()->first()->country_code,
            ],
        ];

        if ($addressSecondLine = $order->shipping->address_second_line) {
            $shipping['address']['address_line_2'] = $addressSecondLine;
        }

        return $shipping;
    }

    public function isApprovedCheckout(Order $order, string $id): bool
    {
        $checkout = $order->checkouts()
            ->find($id);

        if (!$checkout) {
            return false;
        }

        $data = $this->setAccessToken()
            ->sendRequest(
                self::METHOD_GET,
                __config('paypal.urls.methods.checkout_show', ['id' => $id])
            );

        if (empty($data)) {
            return false;
        }

        if ($data['status'] !== PayPalCheckoutStatusEnum::APPROVED) {
            return false;
        }

        return $this->approvedCheckoutOrder($data, $checkout);
    }

    private function approvedCheckoutOrder(array $order, ?PayPalCheckout $checkout = null): bool
    {
        if (!$checkout) {
            $checkout = $this->getCheckOutSession($order, self::W_EVENT_ORDER_APPROVED);

            if (!$checkout) {
                return false;
            }
        }

        if ($checkout->checkout_status->value !== PayPalCheckoutStatusEnum::CREATED) {
            return true;
        }

        //If order has approved/completed sessions
        if ($checkout->order->checkouts()
            ->whereIn('checkout_status', [PayPalCheckoutStatusEnum::COMPLETED, PayPalCheckoutStatusEnum::APPROVED])
            ->whereNull('refund_status')
            ->exists()
        ) {
            return true;
        }

        $checkout->checkout_status = $order['status'];

        $checkout->save();

        //Remove other sessions
        $checkout->order->checkouts()
            ->where('id', '<>', $checkout->id)
            ->delete();

        $this->fillShippingAddressFromCheckout($order, $checkout->order);

        return true;
    }

    private function getCheckOutSession(array $webhookOrder, string $event): ?PayPalCheckout
    {
        if ($event === self::W_EVENT_ORDER_APPROVED) {
            if (empty($webhookOrder['id'])) {
                return null;
            }

            return PayPalCheckout::find($webhookOrder['id']);
        }

        if ($event === self::W_EVENT_CAPTURE_COMPLETED) {
            if (empty($webhookOrder['supplementary_data']['related_ids']['order_id'])) {
                return null;
            }

            return PayPalCheckout::find($webhookOrder['supplementary_data']['related_ids']['order_id']);
        }

        if ($event === self::W_EVENT_CAPTURE_REFUND) {
            if (empty($webhookOrder['id'])) {
                return null;
            }

            return PayPalCheckout::whereRefundId($webhookOrder['id'])
                ->first();
        }

        return null;
    }

    protected function fillShippingAddressFromCheckout(array $webhookData, Order $order): void
    {
        try {
            $hook = reset($webhookData['purchase_units']);

            if (
                empty($hook['shipping'])
                || empty($hook['shipping']['name'])
                || empty($hook['shipping']['address'])
            ) {
                info('Hook shipping address is empty');

                return;
            }

            $address = $hook['shipping']['address'];

            if (!$state = $this->stateService->getStateByShortName($address['admin_area_1'])) {
                info('Hook admin_area_1 is: ' . $address['admin_area_1']);

                return;
            }

            if ($address['country_code'] !== 'US') {
                info('Hook country code is: ' . $address['country_code']);

                return;
            }

            makeTransaction(
                static function () use ($order, $hook, $state, $address) {
                    $shipping = $order->shipping;

                    $shipping->address_first_line = $address['address_line_1'];
                    $shipping->address_second_line = $address['address_line_2'] ?? null;
                    $shipping->city = $address['admin_area_2'];
                    $shipping->state = $state;
                    $shipping->zip = $address['postal_code'];

                    $fullName = explode(' ', $hook['shipping']['name']['full_name']);

                    $shipping->first_name = $fullName[0];
                    $shipping->last_name = $fullName[1];

                    $shipping->save();
                }
            );
        } catch (Throwable $e) {
            logger($e);
        }
    }

    public function refundPayment(Order $order): void
    {
        $checkout = $order->checkouts()
            ->where('checkout_status', PayPalCheckoutStatusEnum::COMPLETED)
            ->first();

        if (!$checkout) {
            return;
        }

        $refund = $this->setAccessToken()
            ->sendRequest(
                self::METHOD_POST,
                __config('paypal.urls.methods.refund', ['capture_id' => $checkout->capture_id])
            );

        $checkout->refund_status = $refund['status'];
        $checkout->refund_id = $refund['id'];

        $checkout->save();
    }

    public function verifyWebhook(PayPalWebhookSignatureDto $dto): bool
    {
        if (!$dto->isExistsAllData()) {
            return false;
        }

        $algorithms = openssl_get_md_methods(true);
        $webhookAuthAlgorithm = mb_convert_case($dto->getAuthAlgo(), MB_CASE_LOWER);

        foreach ($algorithms as $algorithm) {
            if (!Str::contains(mb_convert_case($algorithm, MB_CASE_LOWER), $webhookAuthAlgorithm)) {
                continue;
            }

            $usedAlgorithm = $algorithm;
            break;
        }

        if (empty($usedAlgorithm)) {
            return false;
        }

        $pubKey = openssl_pkey_get_public(
            file_get_contents($dto->getCertUrl())
        );

        $result = openssl_verify(
            $dto->getTransmissionId() . '|' . $dto->getTransmissionTime() . '|' . config(
                'paypal.webhook_id'
            ) . '|' . crc32($dto->getBody()),
            base64_decode($dto->getTransmissionSig()),
            openssl_pkey_get_details($pubKey)['key'],
            $usedAlgorithm
        );

        return $result === 1;
    }

    public function webhookProcessing(array $webhook): bool
    {
        if (empty($webhook['event_type'])) {
            return false;
        }

        return match ($webhook['event_type']) {
            self::W_EVENT_ORDER_APPROVED => $this->approvedCheckoutOrder($webhook['resource']),
            self::W_EVENT_CAPTURE_COMPLETED => $this->completeCheckoutPayment($webhook['resource']),
            self::W_EVENT_CAPTURE_REFUND => $this->refundCheckoutPayment($webhook['resource']),
            default => false
        };
    }

    private function completeCheckoutPayment(array $webhookOrder): bool
    {
        $checkout = $this->getCheckOutSession($webhookOrder, self::W_EVENT_CAPTURE_COMPLETED);

        if (!$checkout) {
            return false;
        }

        if ($checkout->checkout_status === PayPalCheckoutStatusEnum::COMPLETED) {
            return true;
        }

        $checkout->checkout_status = PayPalCheckoutStatusEnum::COMPLETED;

        $checkout->save();

        $this->completePayment($checkout);

        return true;
    }

    private function completePayment(PayPalCheckout $checkout): void
    {
        resolve(OrderService::class)->setPaid($checkout->order);
    }

    private function refundCheckoutPayment(array $webhookOrder): bool
    {
        $checkout = $this->getCheckOutSession($webhookOrder, self::W_EVENT_CAPTURE_REFUND);

        if (!$checkout) {
            return false;
        }

        if ($checkout->refund_status->value === $webhookOrder['status']) {
            return true;
        }

        $checkout->refund_status = $webhookOrder['status'];
        $checkout->save();

        return true;
    }

    public function checkChangeCheckoutStatus(PayPalCheckout $checkout): void
    {
        switch ($checkout->checkout_status) {
            case PayPalCheckoutStatusEnum::APPROVED:
                $this->setAccessToken()
                    ->capturePaymentOrder($checkout);
                break;
            case PayPalCheckoutStatusEnum::COMPLETED:
                $this->completePayment($checkout);
                break;
        }
    }

    private function capturePaymentOrder(PayPalCheckout $checkout): void
    {
        try {
            $response = $this->sendRequest(
                self::METHOD_POST,
                __config('paypal.urls.methods.capture_payment', ['id' => $checkout->id])
            );

            $checkout->checkout_status = $response['status'];
            $checkout->capture_id = $response['purchase_units'][0]['payments']['captures'][0]['id'];

            $checkout->save();
        } catch (PayPalSomethingWentWrongException) {
        }
    }

    public function checkChangeRefundStatus(PayPalCheckout $checkout): void
    {
        switch ($checkout->refund_status) {
            case PayPalRefundStatusEnum::COMPLETED:
                $this->completeRefunded($checkout);
                break;
        }
    }

    private function completeRefunded(PayPalCheckout $checkout): void
    {
        resolve(OrderService::class)->setRefunded($checkout->order);
    }

    public function checkIntegration(Command $command): bool
    {
        if (!config('paypal.client_id')) {
            $command->error('You need to set "PAYPAL_CLIENT_ID" in .env file.');
            $fail = true;
        }

        if (!config('paypal.client_secret')) {
            $command->error('You need to set "PAYPAL_CLIENT_SECRET" in .env file.');
            $fail = true;
        }

        if (!config('paypal.mode')) {
            $command->error('You need to set "PAYPAL_MODE" in .env file.');
            $fail = true;
        }

        if (!empty($fail)) {
            return false;
        }

        $webhookId = config('paypal.webhook_id');

        if (!$webhookId) {
            $command->warn('You need to set "PAYPAL_WEBHOOK_ID" in .env file.');
        }

        try {
            $this->setAccessToken(false);
        } catch (Exception) {
            $command->error('Invalid "PAYPAL_CLIENT_ID" or "PAYPAL_CLIENT_SECRET"');
            return false;
        }

        if (!$this->checkWebhookUrl()) {
            $command->error('You must use HTTPS protocol in your webhook route.');
            return false;
        }

        $webhooks = $this->getWebhookList();

        if ($webhooks === null) {
            $command->error('Failed to get webhooks list.');

            return false;
        }

        $webhooks = collect($webhooks)->mapWithKeys(
            fn(array $item, $key) => [
                $item['id'] => [
                    'url' => $item['url'],
                    'events' => array_column($item['event_types'], 'name')
                ]
            ]
        );

        if ($webhookId && $webhooks->has($webhookId)) {
            if (!$this->checkWebhookUrl($webhooks[$webhookId])) {
                $command->warn('Your webhook settings have incorrect URL address.');
                $command->comment('Trying to search correct webhook...');
            } else {
                if (!$this->checkWebhookEvents($webhooks[$webhookId]['events'])) {
                    $command->warn('Your webhook has incorrect events list');
                    $command->comment('Trying to update events list... ');

                    if (!$this->updateWebhookEventsList($webhookId)) {
                        $command->error('Failed to update webhook\'s events list.');
                        return false;
                    }

                    $command->info('Success to update webhook\'s events list.');
                } else {
                    $command->info('Your webhook settings have correct setting.');
                }
                return true;
            }
            unset($webhooks[$webhookId]);
        }

        foreach ($webhooks as $id => $webhook) {
            if (!$this->checkWebhookUrl($webhook)) {
                continue;
            }

            $command->info('Found the webhook with the same URL. Webhook ID: ' . $id);
            $command->comment('Checking its events...');

            if (!$this->checkWebhookEvents($webhook['events'])) {
                $command->warn('This webhook has incorrect events list');
                $command->comment('Trying to update events list... ');

                if (!$this->updateWebhookEventsList($id)) {
                    $command->error('Failed to update webhook\'s events list.');
                    return false;
                }

                $command->info('Success to update webhook\'s events list.');
            } else {
                $command->info('Events list is correct.');
            }

            $command->info(
                $webhookId ?
                    'Please change "PAYPAL_WEBHOOK_ID" in .env file. From "' . $webhookId . '" to "' . $id . '"' :
                    'Please change "PAYPAL_WEBHOOK_ID" in .env file: ' . $id
            );
            return true;
        }

        $command->info('Trying to create webhook...');

        $id = $this->createWebhook();

        if ($id === null) {
            $command->error('Failed to create webhook.');

            return false;
        }
        $command->info(
            $webhookId ?
                'Please change "PAYPAL_WEBHOOK_ID" in .env file. From "' . $webhookId . '" to "' . $id . '"' :
                'Please change "PAYPAL_WEBHOOK_ID" in .env file: ' . $id
        );
        return true;
    }

    private function checkWebhookUrl(?array $webhook = null): bool
    {
        $url = route(config('paypal.urls.webhook'));

        if ($webhook !== null) {
            return $url === $webhook['url'];
        }

        return parse_url($url, PHP_URL_SCHEME) === 'https';
    }

    private function getWebhookList(): ?array
    {
        try {
            $webhooks = $this->sendRequest(
                self::METHOD_GET,
                config('paypal.urls.methods.webhook')
            );
            return !empty($webhooks['webhooks']) ? $webhooks['webhooks'] : [];
        } catch (Exception) {
            return null;
        }
    }

    private function checkWebhookEvents(array $events): bool
    {
        return empty(array_diff($events, self::W_EVENTS)) && empty(array_diff(self::W_EVENTS, $events));
    }

    private function updateWebhookEventsList(string $webhookId): bool
    {
        try {
            $webhook = $this->sendRequest(
                self::METHOD_PATCH,
                __config('paypal.urls.methods.webhook_update', ['id' => $webhookId]),
                [
                    'json' => [
                        [
                            'op' => 'replace',
                            'path' => '/event_types',
                            'value' => array_map(
                                fn(string $item) => [
                                    'name' => $item
                                ],
                                self::W_EVENTS
                            )
                        ]
                    ]
                ]
            );
            return !empty($webhook['id']);
        } catch (Exception) {
            return false;
        }
    }

    private function createWebhook(): ?string
    {
        try {
            $webhook = $this->sendRequest(
                self::METHOD_POST,
                config('paypal.urls.methods.webhook'),
                [
                    'json' => [
                        'url' => route(config('paypal.urls.webhook')),
                        'event_types' => array_map(
                            fn(string $item) => [
                                'name' => $item
                            ],
                            self::W_EVENTS
                        )
                    ]
                ]
            );
            return !empty($webhook['id']) ? $webhook['id'] : null;
        } catch (Exception) {
            return null;
        }
    }
}
