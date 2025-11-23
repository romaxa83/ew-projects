<?php

namespace WezomCms\Orders\Drivers\Payment;

use Illuminate\Http\Request;
use WezomCms\Orders\Contracts\OnlinePaymentInterface;
use WezomCms\Orders\Contracts\PaymentDriverInterface;
use WezomCms\Orders\Models\Order;

class LiqPay implements PaymentDriverInterface, OnlinePaymentInterface
{
    public const KEY = 'liq-pay';
    private const HOST = 'https://www.liqpay.ua/api/3/checkout';

    /**
     * @var string|null
     */
    private $publicKey;

    /**
     * @var string|null
     */
    private $privateKey;

    /**
     * @var bool
     */
    private $sandBox;

    /**
     * LiqPay constructor.
     */
    public function __construct()
    {
        $this->publicKey = settings('payments.liq_pay.public_key');
        $this->privateKey = settings('payments.liq_pay.private_key');
        $this->sandBox = (bool) settings('payments.liq_pay.sandbox', false);
    }

    /**
     * Generate link to payment system.
     *
     * @param  Order  $order
     * @param  string  $resultUrl  - Url where the buyer would be redirected.
     * @param  string  $serverUrl
     * @return string|null
     */
    public function redirectUrl(Order $order, string $resultUrl, string $serverUrl): ?string
    {
        $data = $this->prepareData($order, $resultUrl, $serverUrl);

        $signature = base64_encode(sha1($this->privateKey . $data . $this->privateKey, 1));

        return static::HOST . '?' . http_build_query(compact('data', 'signature'));
    }

    /**
     * Handle server request from payment system.
     *
     * @param  Request  $request
     * @return bool - is successfully payed
     */
    public function handleServerRequest(Request $request): bool
    {
        if ($request->method() !== 'POST') {
            return false;
        }

        $signature = base64_encode(sha1($this->privateKey . $request->get('data') . $this->privateKey, 1));

        $data = json_decode(base64_decode($request->get('data')), true);
        if ($signature !== $request->get('signature')) {
            logger('LiqPay - Wrong signature', ['post' => $data]);

            return false;
        }

        return array_get($data, 'status') === 'success';
    }

    /**
     * @param  Order  $order
     * @param  string  $resultUrl
     * @param  string  $serverUrl
     * @return string
     */
    protected function prepareData(Order $order, string $resultUrl, string $serverUrl): string
    {
        $data = [
            'public_key' => $this->publicKey,
            'action' => 'pay',
            'version' => 3,
            'amount' => $order->whole_purchase_price,
            'currency' => $order->currency_iso_code,
            'description' => __('cms-orders::admin.payments.Payment order', ['order' => $order->id]),
            'order_id' => $order->id,
            'result_url' => $resultUrl,
            'server_url' => $serverUrl,
        ];

        if ($this->sandBox) {
            $data['sandbox'] = 1;
        }

        return base64_encode(json_encode($data));
    }
}
