<?php

namespace App\Services\Payments;

use App\Models\Orders\Parts\Order;
use Exception;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal;
use Throwable;

class PaypalService
{
    protected PayPal $provider;

    protected array|string $token = [];

    /**
     * @throws Exception
     * @throws Throwable
     */
    public function __construct()
    {
        $this->provider = new PayPal();
        $this->provider->setApiCredentials(config('paypal'));
        $this->token = $this->provider->getAccessToken();
    }

    /**
     * @throws Throwable
     */
    public function createPaymentOrder(Order $order): ?string
    {
        $paymentResource = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => $order->id,
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => $order->getAmount(),
                    ]
                ],
            ],
            'payment_source' => [
                'paypal' => [
                    'experience_context' => [
                        'user_action' => 'PAY_NOW',
                        'return_url' => route('site.thanks-page', ['order_id' => $order->order_number]),
                        'cancel_url' => route('site.thanks-page', ['order_id' => $order->order_number]),
                    ]
                ],
            ],
        ];

        $response = $this->provider->createOrder($paymentResource);

        if (isset($response['id'])) {
            foreach ($response['links'] as $link) {
                if ($link['rel'] === 'payer-action') {
                    return $link['href'];
                }
            }
        }

        return null;
    }

    /**
     * @throws Throwable
     */
    public function webhooksVerifications(Request $request): bool
    {
        $paymentResource = [
            'auth_algo' => $request->header('PAYPAL-AUTH-ALGO'),
            'cert_url' => $request->header('PAYPAL-CERT-URL'),
            'transmission_id' => $request->header('PAYPAL-TRANSMISSION-ID'),
            'transmission_sig' => $request->header('PAYPAL-TRANSMISSION-SIG'),
            'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
            'webhook_id' => config('paypal.webhooks.status'),
            'webhook_event' => $request->input(),
        ];

        $response = $this->provider->verifyWebHook($paymentResource);

        if (isset($response['verification_status']) && $response['verification_status'] === 'SUCCESS') {
            return true;
        }

        return false;
    }
}
