<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\{JsonResponse, Request};
use Symfony\Component\HttpFoundation\StreamedResponse;
use RingCentral, Cache, Exception;

/**
 * RingCentral API requests.
 */
class RingCentralController extends Controller
{
    private int $accountId;
    private RingCentral\SDK\Platform\Platform $platform;

    /**
     * Connection data set RingCentral.
     * @throws RingCentral\SDK\Http\ApiException
     */
    public function __construct()
    {
        $url = RingCentral\SDK\SDK::SERVER_SANDBOX;
        if (config('app.ring_central.is_prod')) {
            $url = RingCentral\SDK\SDK::SERVER_PRODUCTION;
        }

        $SDK = new RingCentral\SDK\SDK(config('app.ring_central.client'), config('app.ring_central.token'), $url,
            'CRM API', '1.0.0');
        $this->accountId = config('app.ring_central.account_id');

        $this->platform = $SDK->platform();
        $this->platform->login(config('app.ring_central.login'), '', config('app.ring_central.password'));

        if (!$this->platform->loggedIn()) {
            throw new  Exception('Алярма, не могу залогинится на RingCentral');
        }
    }

    /**
     * Get a list of calls to order.
     * @param  Request  $request
     * @param  Order  $order
     * @return JsonResponse
     */
    public function ajaxActivityCalls(Request $request, Order $order): JsonResponse
    {
        $response = Cache::remember('calls_'.(int) $request->order_id, now()->addMinutes(),
            function () use ($request, $order) {
                $order = $order->with('client', 'client.phones')->findOrFail((int) $request->order_id);

                $records = [];

                if (isset($order->client) && $order->client->phones) {
                    foreach ($order->client->phones as $phone) {
                        $res = $this->findCalls($phone->value, $order)['records'];
                        if ($res) {
                            $records = [...$records, ...$res];
                        }
                    }
                }

                return $records;
            });

        return response()
            ->json([
                'success' => true,
                'records' => $response,
            ]);
    }

    /**
     * Get a call recording.
     * @param  Request  $request
     * @return StreamedResponse
     * @throws RingCentral\SDK\Http\ApiException
     */
    public function ajaxProxyMedia(Request $request): StreamedResponse
    {
        $url = "/restapi/v1.0/account/{$this->accountId}/recording/{$request->id}/content";
        $fileContent = $this->platform->get($url, [])->raw();

        return response()
            ->stream(function () use ($fileContent) {
                echo $fileContent;
            });
    }

    /**
     * Find calls for order.
     * @param $phone
     * @param $order
     * @return array
     * @throws RingCentral\SDK\Http\ApiException
     */
    private function findCalls($phone, $order): array
    {
        $params = [
            'phoneNumber' => $phone,
            'dateFrom' => $order->created_at->modify('-2 days midnight')->toIso8601ZuluString(),
        ];

        // Order with status closed
        if (in_array((int) $order->status_id, [9, 10, 12, 13, 14, 19])) {
            $params['dateTo'] = $order->updated_at->modify('+5 days midnight')->toIso8601ZuluString();
        } else {
            $params['dateTo'] = $order->updated_at->modify('+3 month')->toIso8601ZuluString();
        }

        return $this->platform->get("/restapi/v1.0/account/{$this->accountId}/extension/~/call-log", $params)
            ->jsonArray();
    }

    /**
     * TMP method for testing.
     * @return void
     * @throws Exception
     */
    public function foo(): void
    {

        try {
//            $this->setWebHook();

            $response = $this->platform->get('/account/~/extension/~');

            // Get Company Call Log Records https://developers.ringcentral.com/api-reference/Call-Log/readCompanyCallLog
//            $response = $this->platform->get("/restapi/v1.0/account/{$this->accountId}/call-log", []);

//             Get User Call Log Records https://developers.ringcentral.com/api-reference/Call-Log/readUserCallLog
//            $response = $this->platform->get("/restapi/v1.0/account/{$this->accountId}/extension/~/call-log", [
//                'dateFrom' => '2021-12-10T00:00:00.534Z',
//                'perPage' => 15,
//                'limit' => 2,
//            ]);
//            $response = $this->platform->get("/restapi/v1.0/account/{$this->accountId}/extension/308371004/call-log", []);


//            $response = $this->platform->get("/restapi/v1.0/account/{$this->accountId}/recording/10578337005", []);
//            $response = $this->platform->get("/restapi/v1.0/account/{$this->accountId}/recording/10578337005/content", []);

//            $response = $this->platform->get("/restapi/v1.0/subscription", []);

            // Devices https://developers.ringcentral.com/api-reference/Devices/readDeviceSIPInfo
            $response = $this->platform->get("/restapi/v1.0/account/~/extension/~/device");


//            $response = $this->platform->post("/restapi/v1.0/account/~/extension/~/ring-out", [
//                'from' => [
//                    'phoneNumber' => '101',
////                    'forwardingNumberId' => '<ENTER VALUE>'
//                ],
//                'to' => [
//                    'phoneNumber' => '103'
//                ],
////                'callerId' => [
////                    'phoneNumber' => '<ENTER VALUE>'
////                ],
////                'playPrompt' => true,
////                'country' => [
////                    'id' => '<ENTER VALUE>'
////                ]
//            ]);


            // Call https://developers.ringcentral.com/api-reference/Call-Control/createCallOutCallSession
            $response = $this->platform->post("/restapi/v1.0/account/~/telephony/call-out", [
                'from' => [
                    'deviceId' => '804059469012'
                ],
                'to' => [
                    'phoneNumber' => '+18004444444',
//                    'extensionNumber' => '103'
                ]
            ]);


            // Drop Call Session
//            $response = $this->platform->delete("/restapi/v1.0/account/~/telephony/sessions/s-a0d7b2910eae3z17e05af1818z4dbd3ba0000");


            dump($response->jsonArray());
        } catch (\RingCentral\SDK\Http\ApiException $e) {
            // Getting error messages using PHP native interface
            print 'Expected HTTP Error: '.$e->getMessage().PHP_EOL;

            // In order to get Request and Response used to perform transaction:
            $apiResponse = $e->apiResponse();
            print_r($apiResponse->request());
            print_r($apiResponse->response());

            // Another way to get message, but keep in mind, that there could be no response if request has failed completely
            dump($e);
        }
    }

    /**
     * Set WebHook url.
     * @return void
     */
    protected function setWebHook()
    {
        try {
            $list_hooks = $this->platform->get("/restapi/v1.0/subscription", [])->jsonArray();

            if ($list_hooks['records']) {
                // Отрубаем старые вебхуки
                foreach ($list_hooks['records'] as $v) {
                    $this->platform->delete("/restapi/v1.0/subscription/{$v['id']}", []);
                }
            }

            // Add WH
            $apiResponse = $this->platform->post('/subscription', [
                'eventFilters' => [
//                    '/restapi/v1.0/account/~/extension/~/message-store/instant?type=SMS',
                    '/restapi/v1.0/account/~/extension/~/incoming-call-pickup',
//                    '/restapi/v1.0/account/~/extension/~/ringout'
                ],
                'deliveryMode' => [
                    'transportType' => 'WebHook',
//                    'address' => 'https://d2fc-94-231-33-81.ngrok.io/webhook/ring'
                    'address' => 'http://94.231.33.81:51413/webhook/ring'
                ]
            ]);
            print_r('Set Hook Response: '.$apiResponse->text());

            dump($list_hooks);
        } catch (\RingCentral\SDK\Http\ApiException $e) {
            // Getting error messages using PHP native interface
            print 'Expected HTTP Error: '.$e->getMessage().PHP_EOL;

            // In order to get Request and Response used to perform transaction:
            $apiResponse = $e->apiResponse();
            print_r($apiResponse->request());
            print_r($apiResponse->response());

            // Another way to get message, but keep in mind, that there could be no response if request has failed completely
            print '  Message: '.$e->apiResponse->response()->error().PHP_EOL;
        } catch (Exception $e) {
            print_r('Set Hook Exception: '.$e->getMessage());
        }
    }


    public function webhookSave(Request $request): JsonResponse
    {
        // https://developers.ringcentral.com/guide/notifications/webhooks/quick-start
        if (!empty($request->header('validation-token'))) {
            // The answer is needed to install the hook + The response time is important, so as not to be stupid.
            return response()
                ->json([
                    'success' => true,
                ])
                ->header('Validation-Token', $request->header('validation-token'));
        }

        $inputJSON = $request->getContent();
        if ($inputJSON) {
            $payload = json_decode($inputJSON, true, 512, JSON_THROW_ON_ERROR);

            file_put_contents(storage_path('app/public/ringCentral'), now()->toDateTimeString().PHP_EOL.
                print_r($payload, true).PHP_EOL.PHP_EOL, FILE_APPEND);
        }


        return response()
            ->json([
                'success' => true,
            ]);
    }
}
