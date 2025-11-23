<?php

namespace App\Http\Controllers\Import;

use App\Http\Controllers\Controller;
use App\Http\Controllers\WaypointController;
use App\Models\Order;
use App\Models\Import\Authorize\{Account, Transaction};
use App\Models\Order\Payment;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\{Request, JsonResponse};
use Carbon\Carbon;
use Log, App, DB, Config, Auth, Exception, Http;

/**
 * Generating requests for Authorize, apply payments etc.
 */
class AuthorizePaymentController extends Controller
{
    /**
     * API url.
     * @var string
     */
    private string $url = 'https://api.authorize.net/xml/v1/request.api';

    /**
     * prod OR dev mode
     * @var string
     */
    private string $mode; // prod OR dev

    /**
     * Buffer of transactions.
     * @var array
     */
    private array $existsTransactions = [];

    public function __construct()
    {
        $this->mode = config('payments.authorize.mode');

        if ($this->mode === 'dev') {
            // https://sandbox.authorize.net/
            // Dev account => login: FsDevAccount77 pwd: Qwe123qwe key: Simon
            $this->url = 'https://apitest.authorize.net/xml/v1/request.api';
        }
    }

    /**
     * Get View List of Transactions.
     * @return Renderable
     */
    public function home(): Renderable
    {
        return view('layouts.render.with-container', [
            'component' => 'report-authorize-transactions',
            'title' => 'Authorize Transactions',
            'breadcrumbs' => [
                [
                    'title' => 'Reports',
                ],
                [
                    'title' => 'Authorize',
                ],
                [
                    'title' => 'Transactions',
                ]
            ]
        ]);
    }

    /**
     * Processing form submission.
     * @param  Request  $request
     * @return JsonResponse
     * @throws \JsonException
     */
    public function paymentProcess(Request $request): JsonResponse
    {
        if ($request->type === 'card_charge') {
            return $this->createTransactionRequest($request);
        }

        return response()
            ->json([
                'success' => false,
                'msg' => 'Payment Type in development...',
            ]);
    }

    /**
     * Create a payment transaction.
     * @param $request
     * @return JsonResponse
     * @throws \JsonException
     */
    private function createTransactionRequest($request): JsonResponse
    {
        $account = Account::whereDivisionId($request->branch_id)->firstOrFail();

        [$month, $year] = explode('/', $request->card['expire']);

        $address = (new WaypointController())->getAddressInfo($request->client['zip']);

        $userField = [
            [
                'name' => 'user_id',
                'value' => Auth::id()
            ],
            [
                'name' => 'order_id',
                'value' => $request->order_id
            ],
            [
                'name' => 'email',
                'value' => $request->client['email']
            ]
        ];

        $order = Order::find($request->order_id);
        if ($order) {
            $userField[] = [
                'name' => 'client_id',
                'value' => $order->client_id
            ];
        }

        // ############################
        // #       W A R N I N G      #
        // Эта херь требовательна к порядку полей как данные заходят!
        // This crap is demanding on the order of the fields as the data comes in!
        $form = [
            'createTransactionRequest' => [
                'merchantAuthentication' => $this->authorizeAuth($account),
//                'refId' => $request->order_id,
                'transactionRequest' => [
                    'transactionType' => 'authCaptureTransaction',
                    'amount' => $request->amount,
                    'currencyCode' => 'USD',
                    'payment' => [
                        'creditCard' => [
                            'cardNumber' => $this->onlyNumbers($request->card['number']),
                            'expirationDate' => "20{$year}-{$month}",
                            'cardCode' => $request->card['cvv']
                        ]
                    ],
                    'profile' => [
                        'createProfile' => true
                    ],
                    'order' => [
                        'invoiceNumber' => $request->order_id,
                        'description' => 'Order in CRM'
                    ],
                    'customer' => [
                        'id' => ($order && $order->client_id ? $order->client_id : $request->order_id),
                        'email' => $request->client['email'],
                    ],
                    'billTo' => [
                        'firstName' => $request->client['first_name'],
                        'lastName' => $request->client['last_name'],
                        'address' => $request->client['address'],
//                        'company' => 'None',
                        'city' => $address['address_data']['locality'] ?? '',
                        'state' => mb_substr(($address['address_data']['administrative_area_level_1'] ?? 'NA'), 0, 2),
                        'zip' => $request->client['zip'],
                        'country' => 'USA'
                    ],
//                    'shipTo' => [
//                        'firstName' => $request->client['first_name'],
//                        'lastName' => $request->client['last_name'],
//                        'address' => $request->client['address'],
//                        'company' => '',
//                        'city' => $address['address_data']['locality'] ?? '',
//                        'state' => mb_substr(($address['address_data']['administrative_area_level_1'] ?? 'NA'), 0, 2),
//                        'zip' => $request->client['zip'],
//                        'country' => 'USA'
//                    ],
                    'userFields' => [
                        'userField' => $userField
                    ],
                ]
            ]
        ];

        $transactions = Http::retry(3, 10000)->post($this->url, $form); // 10 sec

        $resp = trim($transactions->body(), '﻿');
        $resp = json_decode($resp, true, 512, JSON_THROW_ON_ERROR);

        Log::info('Log of payment Authorize transaction #'.$request->order_id, $resp);

        if (!isset($resp['transactionResponse']['errors']) && $resp['messages']['resultCode'] === 'Ok') {
            $msg = $resp['transactionResponse']['messages'][0]['description'] ?? 'o_0 Unknown Authorize response';


//            dd($resp);

            try {
                $this->joinSuccessTransaction($account, $request->branch_id, $request->order_id,
                    $resp['transactionResponse']['transId'], $request->amount, $request->in_total);
            } catch (Exception $e) {
                report($e);

                return response()
                    ->json([
                        'success' => true,
                        'msg' => 'Transaction created but in report not joined by Order ID, You must join manually in report. '.$msg,
                    ]);
            }

            return response()
                ->json([
                    'success' => true,
                    'msg' => $msg,
                ]);
        }
        return response()
            ->json([
                'success' => false,
                'msg' => $resp['transactionResponse']['errors'][0]['errorText'],
            ]);
    }

    /**
     * Autocomplete an order number by order ID.
     * @param  Request  $request
     * @return JsonResponse
     */
    public function orderIDAutocomplete(Request $request): JsonResponse
    {
        $q = $this->onlyNumbers($request->q);

        $data = Order::query()
            ->with('division:id,short')
            ->where('id', 'LIKE', $q.'%')->take(20)
            ->get(['id', 'client_id', 'division_id'])
            ->take(15)
            ->transform(function ($item) {
                return [
                    'id' => $item->id,
                    'client_id' => $item->client_id,
                    'branch_id' => $item->division_id,
                    'text' => $item->division->short.' '.$item->id,
                ];
            })
            ->all();

        return response()
            ->json([
                'success' => true,
                'items' => $data
            ]);
    }

    /**
     * List of possible statuses.
     * @param  Transaction  $transaction
     * @return JsonResponse
     */
    public function statusesAutocomplete(Transaction $transaction): JsonResponse
    {
        $statuses = $transaction->groupBy('status')->orderBy('status')->get(['status'])
            ->transform(function ($item) {
                return [
                    'id' => $item->status,
                    'text' => $item->status,
                ];
            });

        return response()
            ->json([
                'success' => true,
                'data' => [
                    'results' => $statuses,
                    'pagination' => [
                        'more' => false
                    ]
                ]
            ]);
    }

    /**
     * Bypass all active Authorize accounts either with a specific ID.
     * @param $account_id
     * @param  int  $days  How many days' selection synchronization. (7 by default)
     * @throws \JsonException
     */
    public function import($account_id, int $days = 7): void
    {
        $accounts = Account::with('transactions:id,account_id,status')
            ->when($account_id, function ($q, $account_id) {
                return $q->whereId($account_id);
            }, function ($q) {
                return $q->whereActive(1);
            })
            ->get();
        foreach ($accounts as $v) {
            $this->existsTransactions = $v->transactions->pluck('status', 'id')->all();
            $this->authorizeImportByAccount($v, $days);
        }
    }

    /**
     * Get list of Transactions.
     * @param  Request  $request
     * @param  Transaction  $transaction
     * @return JsonResponse
     */
    public function report(Request $request, Transaction $transaction): JsonResponse
    {

        $orderKey = in_array($request->order['key'],
            ['id', 'account_id', 'status', 'amount', 'updated_at']) ? $request->order['key'] : 'id';

        $transactions = $transaction
            ->whereBetween('submitTimeUTC', [$request->date_start, $request->date_end])
            ->with('account:id,title')
            ->whereHas('account', function ($q) use ($request) {
                $q->where('division_id', $request->session()->get('division.id'));
            })
            ->when($request->filters['order_ids'], function ($q, $ids) {
                $q->whereIn('miscs->manager->order_id', $ids);
            })
            ->when($request->filters['status_ids'], function ($q, $statuses) {
                $q->whereIn('status', $statuses);
            })
            ->orderBy($orderKey, $request->order['isDesc'] ? 'desc' : 'asc')
            ->paginate();

        return response()
            ->json([
                'success' => true,
                'paginate' => $transactions,
            ]);
    }

    /**
     * Saving manager notes for Transaction.
     * @param  Request  $request
     * @param  Transaction  $transaction
     * @return JsonResponse
     */
    public function saveManagerData(Request $request, Transaction $transaction): JsonResponse
    {
        $record = $transaction->findOrFail($request->id);

        $miscs = $record->miscs;
        $miscs['manager'] = [
            'approved' => (bool) $request->record['approved'],
            'branch_id' => (int) $request->record['branch_id'],
            'order_id' => (int) $request->record['order_id'],
            'comment' => (string) $request->record['comment'],
            'approved_at' => now()->toDateTimeString(),
            'user' => Auth::id(),
        ];
        $record->miscs = $miscs;
        $record->timestamps = false;
        $record->save();

        return response()
            ->json([
                'success' => true,
            ]);
    }

    /**
     * Get batch listing of transactions from API for future loading.
     * @throws \JsonException
     */
    private function authorizeImportByAccount($account, $days): void
    {
        $batches = Http::retry(3, 10000)
            ->post($this->url, [
                'getSettledBatchListRequest' => [
                    'merchantAuthentication' => $this->authorizeAuth($account),
                    'firstSettlementDate' => now()->sub('days', $days)->toDateString().'T00:00:00Z',
                    'lastSettlementDate' => now()->toDateString().'T23:59:59Z'
                ]
            ]);

        $resp = trim($batches->body(), '﻿');
        $resp = json_decode($resp, true, 512, JSON_THROW_ON_ERROR);

        foreach ($resp['batchList'] ?? [] as $v) {
            if (app()->runningInConsole()) {
                echo PHP_EOL.'batchList '.$v['batchId'].PHP_EOL;
            }
            $this->authorizeGetTransactions($account, [
                'batchId' => $v['batchId'],
            ]);
        }

        if (app()->runningInConsole()) {
            echo PHP_EOL.'getUnsettled '.($v['batchId'] ?? 'n/a').PHP_EOL;
        }
        $this->authorizeGetTransactions($account);
    }

    /**
     * Transaction list from batchId.
     * @param $account
     * @param  array  $params
     * @throws \JsonException
     */
    private function authorizeGetTransactions($account, array $params = []): void
    {
        if (isset($params['batchId'])) {
            $q = [
                'getTransactionListRequest' => [
                    'merchantAuthentication' => $this->authorizeAuth($account),
                    'batchId' => $params['batchId'],
                    'paging' => [
                        'limit' => 1000,
                        'offset' => 1
                    ]
                ]
            ];
        } else {
            $q = [
                'getUnsettledTransactionListRequest' => [
                    'merchantAuthentication' => $this->authorizeAuth($account),
                    'sorting' => [
                        'orderBy' => 'submitTimeUTC',
                        'orderDescending' => true,
                    ],
                    'paging' => [
                        'limit' => 1000,
                        'offset' => 1
                    ]
                ]
            ];
        }


        $transactions = Http::retry(3, 10000)
            ->post($this->url, $q);

        $resp = trim($transactions->body(), '﻿'); // Stupid symbol from API
        $resp = json_decode($resp, true, 512, JSON_THROW_ON_ERROR);

        if (isset($resp['transactions'])) {
            foreach ($resp['transactions'] as $transaction) {
                if (isset($this->existsTransactions[$transaction['transId']])) {
                    // Transaction is exists, check need a status update
                    if ($this->existsTransactions[$transaction['transId']] !== $transaction['transactionStatus']) {
                        $account->transactions()->where('id', $transaction['transId'])
                            ->update([
                                'status' => $transaction['transactionStatus']
                            ]);
                        if (app()->runningInConsole()) {
                            echo 'U';
                        }
                    }
                } else {
                    // Adding new record + Get details by transId
                    $t_details = $this->authorizeGetTransactionDetails($account, $transaction['transId']);

                    $account->transactions()->updateOrCreate(
                        [
                            'id' => $transaction['transId'],
                        ],
                        [
                            'account_id' => $account->id,
                            'status' => $transaction['transactionStatus'],
                            'amount' => $transaction['settleAmount'],
                            'submitTimeUTC' => Carbon::parse($transaction['submitTimeUTC']),
                            'miscs' => [
                                'batchId' => $t_details['batch']['batchId'] ?? null,
                                'billTo' => $t_details['billTo'] ?? null,
                                'account' => "{$transaction['accountType']} / {$transaction['accountNumber']}",
                                'customer' => $t_details['customer'] ?? null,
                                'tax' => $t_details['tax'] ?? null,
                                'duty' => $t_details['duty'] ?? null,
                                'shipping' => $t_details['shipping'] ?? null,
                            ],
                        ]);

                    if (app()->runningInConsole()) {
                        echo 'N';
                    }
                }

                if (app()->runningInConsole()) {
                    echo '.';
                }
                //  "transId" => "42884073141"
                //  "submitTimeUTC" => "2021-08-22T20:40:36Z"
                //  "submitTimeLocal" => "2021-08-22T13:40:36"
                //  "transactionStatus" => "settledSuccessfully"
                //  "firstName" => "Charlotte"
                //  "lastName" => "Weiss"
                //  "accountType" => "Discover"
                //  "accountNumber" => "XXXX3070"
                //  "settleAmount" => 750.0
                //  "marketType" => "MOTO"
                //  "product" => "Card Not Present"
            }
        }
    }

    /**
     * Get Transaction Details.
     * @param $account
     * @param $trans_id
     * @return mixed
     * @throws \JsonException
     */
    private function authorizeGetTransactionDetails($account, $trans_id)
    {
        $transaction = Http::retry(3, 10000)
            ->post($this->url, [
                'getTransactionDetailsRequest' => [
                    'merchantAuthentication' => $this->authorizeAuth($account),
                    'transId' => $trans_id,
                ]
            ]);

        $resp = trim($transaction->body(), '﻿');
        $resp = json_decode($resp, true, 512, JSON_THROW_ON_ERROR);
        if ($resp['messages']['resultCode'] !== 'Ok') {
            throw new Exception('Error - '.$resp['messages']['message']['code'].' '.
                $resp['messages']['message']['text']);
        }

        //  "transId" => "42844013256"
        //  "submitTimeUTC" => "2021-08-03T00:33:02.013Z"
        //  "submitTimeLocal" => "2021-08-02T17:33:02.013"
        //  "transactionType" => "authCaptureTransaction"
        //  "transactionStatus" => "settledSuccessfully"
        //  "responseCode" => 1
        //  "responseReasonCode" => 1
        //  "responseReasonDescription" => "Approval"
        //  "authCode" => "09066I"
        //  "AVSResponse" => "Y"
        //  "cardCodeResponse" => "M"
        //  "batch" => array:4 [
        //    "batchId" => "844281605"
        //    "settlementTimeUTC" => "2021-08-03T02:16:56.623Z"
        //    "settlementTimeLocal" => "2021-08-02T19:16:56.623"
        //    "settlementState" => "settledSuccessfully"
        //  ]
        //  "authAmount" => 100.0
        //  "settleAmount" => 100.0
        //  "taxExempt" => false
        //  "payment" => array:1 [
        //    "creditCard" => array:3 [
        //      "cardNumber" => "XXXX1875"
        //      "expirationDate" => "XXXX"
        //      "cardType" => "Visa"
        //    ]
        //  ]
        //  "billTo" => array:5 [
        //    "phoneNumber" => "(312) 339-8724"
        //    "firstName" => "James A"
        //    "lastName" => "Dagonas"
        //    "address" => "2838 Pacific View Trail"
        //    "zip" => "90068"
        //  ]
        //  "recurringBilling" => false
        //  "product" => "Card Not Present"
        //  "marketType" => "MOTO"

        return $resp['transaction'];
    }

    /**
     * AUTH data.
     * @param $account
     * @return array
     */
    private function authorizeAuth($account): array
    {
        return [
            'name' => $this->mode === 'dev' ? '6Y6S3wvH' : $account->login,
            'transactionKey' => $this->mode === 'dev' ? '665522P7TjV8eMg3' : $account->transactionKey,
        ];
    }

    /**
     * Remove non-numeric symbols.
     * @param $q
     * @return array|string|string[]|null
     */
    private function onlyNumbers($q)
    {
        return preg_replace('/[^0-9]/', '', $q);
    }

    /**
     * Get transactions for today + Find and tied up by ID the one for which the payment was made.
     * @param  Account  $account
     * @param  int  $branch_id
     * @param  int  $order_id
     * @param  int  $transId
     * @param  int  $amount
     * @param  int  $in_total
     * @throws \JsonException
     */
    private function joinSuccessTransaction(
        Account $account,
        int $branch_id,
        int $order_id,
        int $transId,
        int $amount,
        int $in_total
    ): void {
        $this->import($account->id, 1);

        if ($account->payment_account_id) {
            $payment = new Payment();
            $payment->user_id = Auth::id();
            $payment->amount = $amount;
            $payment->order_id = $order_id;
            $payment->payment_account_id = $account->payment_account_id;
            $payment->description = 'Authorize Virtual Terminal #'.$transId;
            $payment->in_total_sum = !empty($in_total) ? 1 : 0;
            $payment->save();
        }

        // Find a transaction and attach to the order
        $record = Transaction::find($transId);

        if (!$record) {
            throw new Exception('In logs Transaction not found ID: '.$transId);
        }

        $miscs = $record->miscs;
        $miscs['manager'] = [
            'approved' => true,
            'approved_at' => now()->toDateTimeString(),
            'branch_id' => $branch_id,
            'order_id' => $order_id,
            'comment' => 'From Virtual Terminal',
            'user' => Auth::id(),
        ];
        $record->miscs = $miscs;
        $record->timestamps = false;
        $record->save();
    }
}
