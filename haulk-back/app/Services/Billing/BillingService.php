<?php


namespace App\Services\Billing;


use App\Exceptions\Billing\CompanyIsSubscription;
use App\Exceptions\Billing\CompanyPaymentMethodRequired;
use App\Exceptions\Billing\TransactionUnderReviewException;
use App\Models\Billing\ActiveDriverHistory;
use App\Models\Billing\Invoice;
use App\Models\History\UserHistory;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\DeviceHistory;
use App\Models\Saas\GPS\DevicePayment;
use App\Models\Users\User;
use App\Notifications\Billing\RenewSubscribe;
use App\Notifications\Billing\SendPdfInvoice;
use App\Notifications\Billing\SuccessUnsubscribe;
use App\Notifications\Saas\Invoices\InvoicePaymentPending;
use App\Services\Events\EventService;
use App\Services\Histories\UserHistoryService;
use App\Services\Orders\GeneratePdfService;
use App\Services\Permissions\Payments\PaymentProviderInterface;
use App\Services\Saas\BackofficeService;
use App\Services\Saas\GPS\Devices\DeviceRequestService;
use App\Services\Saas\GPS\Devices\DeviceSubscriptionService;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Throwable;

class BillingService
{
    protected DeviceSubscriptionService $deviceSubscriptionService;

    public function __construct(DeviceSubscriptionService $deviceSubscriptionService)
    {
        $this->deviceSubscriptionService = $deviceSubscriptionService;
    }

    private function getBackofficeService(): BackofficeService
    {
        return resolve(BackofficeService::class);
    }

    private function getUserHistoryService(): UserHistoryService
    {
        return resolve(UserHistoryService::class);
    }

    private function getPaymentService(): PaymentProviderInterface
    {
        return resolve(PaymentProviderInterface::class);
    }

    public function clearCompanyDriverHistory(int $companyId): void
    {
        ActiveDriverHistory::where('carrier_id', $companyId)
            ->delete();
    }

    /**
     * @param $driverCountHistory
     */
    private function clearDriverHistory($driverCountHistory): void
    {
        ActiveDriverHistory::whereIn('id', $driverCountHistory->pluck('id'))
            ->delete();
    }

    private function clearGpsPaymentItems(Company $company, Carbon $start, Carbon $end): void
    {
        $models = DevicePayment::where('company_id', $company->id)
            ->whereBetween('date', [$start->startOfDay(), $end->endOfDay()])
            ->get()
        ;

        foreach ($models as $model){
            /** @var $model DevicePayment */
            DeviceHistory::createPaymentDelete($model, $start, $end);
            $model->delete();
        }
    }

    private function getClearGpsPaymentItems(Company $company, Carbon $start, Carbon $end): array
    {
        return DevicePayment::where('company_id', $company->id)
            ->select(['device_id', 'company_id', 'amount', 'date'])
            ->whereBetween('date', [$start->startOfDay(), $end->endOfDay()])
            ->toBase()
            ->get()
            ->toArray();
    }

    private function getActiveDriverHistory(int $companyId, Carbon $start, Carbon $end)
    {
        return ActiveDriverHistory::select(
            [
                'id',
                'date',
                'driver_count',
                'amount',
            ]
        )->where(
            [
                ['carrier_id', $companyId],
                ['date', '>=', $start->format('Y-m-d')],
                ['date', '<=', $end->format('Y-m-d')],
            ]
        )->orderBy(
            'date'
        )->get();
    }

    private function addDriverHistory($driverHistory, $day): array
    {
        $dayHistory = $driverHistory->where('date', $day);

        return [
            'activated_count' => $dayHistory
                ->where(
                    'operation',
                    UserHistory::STATUS_ACTIVATED
                )->groupBy(
                    'user_id'
                )->count(),
            'deactivated_count' => $dayHistory
                ->where(
                    'operation',
                    UserHistory::STATUS_DEACTIVATED
                )->groupBy(
                    'user_id'
                )->count(),
            'users' => $dayHistory
                ->whereIn(
                    'operation',
                    [
                        UserHistory::STATUS_ACTIVATED,
                        UserHistory::STATUS_DEACTIVATED,
                    ]
                )->map(
                    function ($el) {
                        return [
                            'full_name' => $el->full_name,
                            'email' => $el->email,
                            'operation' => $el->operation,
                        ];
                    }
                )->toArray(),
        ];
    }

    public function getDailyDriverCountForPeriod($driverHistory, Carbon $start, Carbon $end): array
    {
        $countForPeriod = [];

        $period = $start->toPeriod($end);

        foreach ($period as $day) {
            $countForPeriod[$day->format('Y-m-d')] = [
                'driver_count' => 0,
                'amount' => 0,
            ];
        }

        if ($driverHistory->count()) {
            $driverHistory->map(
                function ($el) use (&$countForPeriod) {
                    $countForPeriod[$el->date]['driver_count'] = $el->driver_count;
                    $countForPeriod[$el->date]['amount'] = $el->amount;
                }
            );

            $datesReversed = array_reverse(
                array_keys($countForPeriod)
            );

            foreach ($datesReversed as $date) {
                if ($date !== $driverHistory->last()->date) {
                    $countForPeriod[$date]['driver_count'] = $driverHistory->last()->driver_count;
                    $countForPeriod[$date]['amount'] = $driverHistory->last()->amount;
                } else {
                    break;
                }
            }
        }

        return $countForPeriod;
    }

    public function showPaymentRequiredError(Company $company, Request $request): bool
    {
        if (
            !$request->is(config('billing.restricted_access_exception_urls'))
            && in_array($request->method(), ['POST', 'PUT', 'DELETE'])
        ) {
            return $this->ifHaveReadOnlyAccess($company);
        }

        return false;
    }

    public function ifHaveReadOnlyAccess(Company $company): bool
    {

        if (!$company->hasSubscription()) {
            return true;
        }

        if (!$company->isInTrialPeriod()) {
            if (!$company->hasPaymentMethod()) {
                return true;
            }

            if (!$company->isSubscriptionActive()) {
                return true;
            }

            if ($company->paymentAttemptsCountExhausted()) {
                return true;
            }
        }

        return false;
    }

    public function readOnlyPermissionsFilter(array $permissions): array
    {
        $filtered = [];
        $permissionGroupsMasked = config('billing.permission_groups_masked');

        foreach ($permissionGroupsMasked as $group) {
            if (isset($permissions[$group])) {
                $filtered[$group] = $permissions[$group];
            }
        }

        foreach ($permissions as $group => $list) {
            if (!in_array($group, $permissionGroupsMasked, true)) {
                foreach ($list as $permission) {
                    if ($permission === 'read') {
                        $filtered[$group][] = $permission;
                    }
                }
            }
        }

        return $filtered;
    }

    public function gpsMenuPermissionsFilter(array $permissions, User $user): array
    {
        if($user->getCompany()->paymentAttemptsCountExhausted()){

            if(isset($permissions['gps-menu'])){
                $tmp = [];
                unset($permissions['gps-menu']);

                if($user->isSuperAdmin()){
                    $tmp[] = 'map-visible';
                    $tmp[] = 'device-visible';
                    $tmp[] = 'device-active';
                } elseif ($user->isAdminRole()) {
                    $tmp[] = 'map-visible';
                    $tmp[] = 'device-visible';
                } else {
                    $tmp[] = 'map-visible';
                }

                $permissions['gps-menu'] = $tmp;
            }
        }

        return $permissions;
    }

    public function createDriverHistoryRecord(Company $company, int $driverCount, string $date): ActiveDriverHistory
    {
        $record = new ActiveDriverHistory();
        $record->carrier_id = $company->id;
        $record->driver_count = $driverCount;
        $record->date = $date;
        $record->amount = $driverCount * $this->calculateDriverDailyPrice($company);
        $record->save();

        return $record;
    }

    public function updateDriverHistoryDriversCount(Company $company, ActiveDriverHistory $record, int $driverCount): ActiveDriverHistory
    {
        $driverCount = $driverCount > $record->driver_count ? $driverCount : $record->driver_count;
        $amount = $driverCount * $this->calculateDriverDailyPrice($company);

        if (
            $driverCount > $record->driver_count
            || $amount > $record->amount
        ) {
            $record->driver_count = $driverCount;
            $record->amount = $amount;
            $record->save();
        }

        return $record;
    }

    public function trackCompanyActiveDrivers(Company $company): void
    {
        $count = User::query()
            ->where('carrier_id', $company->id)
            ->active()
            ->onlyDrivers()
            ->count();

        $record = ActiveDriverHistory::where(
            [
                ['carrier_id', $company->id],
                ['date', now()->format('Y-m-d')],
            ]
        )->first();

        if ($record) {
            $this->updateDriverHistoryDriversCount(
                $company,
                $record,
                $count
            );
        } else {
            $this->createDriverHistoryRecord(
                $company,
                $count,
                now()->format('Y-m-d')
            );
        }
    }

    public function calculateEstimatedPayment(Company $company): array
    {
        $plannedPrice = 0;
        $devicePrice = 0;
        $subscription = $company->subscription;

        if ($subscription) {
            $start = $subscription->billing_start;
            $end = $subscription->billing_end;

            $driverHistory = $this->getActiveDriverHistory($company->id, $start, $end);

            foreach ($this->getDailyDriverCountForPeriod($driverHistory, $start, $end) as $day) {
                $plannedPrice += $day['amount'];
            }

            if($company->hasGpsDeviceSubscription()){
                $devicePrice = $company
                    ->gpsDevicePaymentItems()
                    ->sum('amount');
            }

            return [
                'price' => $company->isExclusivePlan() ? 0 : round($plannedPrice, 2) + round($devicePrice, 2),
                'driver_count' => $driverHistory->count() ? $driverHistory->values()->last()->driver_count : 0,
                'next_payment_date' => $subscription->billing_end
                    ? $end->addDay()->timestamp
                    : null,
                'device_price' => $company->isExclusivePlan() ? 0 : round($devicePrice, 2),
                'driver_price' => $company->isExclusivePlan() ? 0 : round($plannedPrice, 2)
            ];
        }

        return [
            'price' => 0,
            'driver_count' => 0,
            'next_payment_date' => null,
        ];
    }


    public function createInvoice(Company $company, Carbon $start, Carbon $end): Invoice
    {
        $plannedPrice = 0;
        $billingData = [];

        $driverHistory = $this->getUserHistoryService()->getDriverHistory(
            $company->id,
            $start->startOfDay()->format('Y-m-d H:i:s'),
            $end->endOfDay()->format('Y-m-d H:i:s')
        );

        $driverCountHistory = $this->getActiveDriverHistory($company->id, $start, $end);

        foreach ($this->getDailyDriverCountForPeriod($driverCountHistory, $start, $end) as $day => $dayData) {
            $plannedPrice += $dayData['amount'];
            $operations = $this->addDriverHistory($driverHistory, $day);

            $billingData[] = [
                'date' => strtotime($day),
                'driver_count' => $dayData['driver_count'],
                'amount' => $dayData['amount'],
                'activated_count' => $operations['activated_count'],
                'deactivated_count' => $operations['deactivated_count'],
                'users' => $operations['users'],
            ];
        }

        $invoice = new Invoice();

        $invoice->carrier_id = $company->id;
        $invoice->company_name = $company->getCompanyName();
        $invoice->billing_start = $start->format('Y-m-d');
        $invoice->billing_end = $end->format('Y-m-d');
        $invoice->amount = round($plannedPrice, 2);
        $invoice->drivers_amount = round($plannedPrice, 2);
        $invoice->billing_data = $billingData;
        $invoice->public_token = hash('sha256', Str::random(60));
        $invoice->is_paid = $invoice->amount === 0;

        if($company->isGPSEnabled()){
            $gpsBillingData = $this->getBillingDataForGpsDevice(
                $company, $start, $end
            );

            $invoice->has_gps_subscription = true;
            $invoice->gps_device_data = $gpsBillingData->toArray();
            $invoice->gps_device_amount = round($gpsBillingData->sum('amount'), 2);
            $invoice->amount += $invoice->gps_device_amount;

            $this->deviceSubscriptionService->changeRate($company->gpsDeviceSubscription);

            $invoice->gps_device_payment_data = $this->getClearGpsPaymentItems($company, $start, $end);

            $this->clearGpsPaymentItems($company, $start, $end);

        }

        $invoice->save();

        return $invoice;
    }

    public function createMonthlyInvoice(Company $company): ?Invoice
    {
        $subscription = $company->subscription;

        if ($subscription) {
            $start = $subscription->billing_start;
            $end = $subscription->billing_end;

            $driverCountHistory = $this->getActiveDriverHistory(
                $company->id,
                $start,
                $end
            );

            $billingData = $this->getBillingData(
                $company->id,
                $driverCountHistory,
                $start,
                $end
            );

            try {
                DB::beginTransaction();

                $invoice = new Invoice();
                $invoice->carrier_id = $company->id;
                $invoice->company_name = $company->getCompanyName();
                $invoice->billing_start = $start->format('Y-m-d');
                $invoice->billing_end = $end->format('Y-m-d');
                $invoice->amount = round($billingData->sum('amount'), 2);
                $invoice->drivers_amount = round($billingData->sum('amount'), 2);
                $invoice->billing_data = $billingData->toArray();
                $invoice->public_token = hash('sha256', Str::random(60));

                if($company->isGPSEnabled()){
                    $gpsBillingData = $this->getBillingDataForGpsDevice(
                        $company, $start, $end
                    );

                    $invoice->has_gps_subscription = true;
                    $invoice->gps_device_data = $gpsBillingData->toArray();
                    $invoice->gps_device_amount = round($gpsBillingData->sum('amount'), 2);
                    $invoice->amount += $invoice->gps_device_amount;

                    $this->deviceSubscriptionService->changeRate($company->gpsDeviceSubscription);

                    $invoice->gps_device_payment_data = $this->getClearGpsPaymentItems($company, $start, $end);

                    $this->clearGpsPaymentItems($company, $start, $end);
                }

                if($company->isExclusivePlan()){
                    $invoice->is_paid = true;
                    $invoice->amount = 0;
                    $invoice->drivers_amount = 0;
                    $invoice->gps_device_amount = 0;
                    $invoice->attempt += 1;
                    $invoice->paid_at = now()->timestamp;
                    $invoice->trans_id = null;
                    $invoice->attempt_history = [
                        [
                            'time' => now()->timestamp,
                            'status' => 'Success',
                            'reason' => 'Not pay because have a Exclusive plan',
                        ]
                    ];
                }

                $invoice->save();

                $this->clearDriverHistory($driverCountHistory);

                DB::commit();

                return $invoice;
            } catch (Exception $e) {
                DB::rollBack();
            }
        }

        return null;
    }

    public function getBillingDataForGpsDevice(Company $company, Carbon $start, Carbon $end): Collection
    {
        $diffDays = $start->diffInDays($end);
        $days = $diffDays ? $diffDays + 1 : 0;

        $tmp = collect();
        DevicePayment::query()
            ->with('device')
            ->selectRaw('count(id) as count_rec, sum(amount) as amount, device_id')
            ->where('company_id', $company->id)
            ->whereBetween('date', [$start->startOfDay(), $end->endOfDay()])
            ->groupBy('device_id')
            ->get()
            ->each(function (DevicePayment $item) use (&$tmp, $days) {
                logger_info('getBillingDataForGpsDevice', [
                    'rec' => $item->count_rec,
                    'day' => $days,
                ]);

                $tmp->push([
                    'days' => $item->count_rec,
                    'amount' => round($item->amount, 2),
                    'activate' => $item->count_rec < $days,
                    'deactivate' => isset($item->device->active_till_at),
                    'active_at' => $item->device->active_at->timestamp ?? null,
                    'active_till' => $item->device->active_till_at->timestamp ?? null,
                    'name' => $item->device->name,
                    'imei' => $item->device->imei,
                ]);
            })
        ;

        return $tmp;
    }

    private function getBillingData(int $companyId, $driverCountHistory, Carbon $start, Carbon $end): Collection
    {
        $billingData = [];

        $driverHistory = $this->getUserHistoryService()->getDriverHistory(
            $companyId,
            $start->startOfDay()->format('Y-m-d H:i:s'),
            $end->endOfDay()->format('Y-m-d H:i:s')
        );

        foreach ($this->getDailyDriverCountForPeriod($driverCountHistory, $start, $end) as $day => $dayData) {
            $operations = $this->addDriverHistory($driverHistory, $day);

            $billingData[] = [
                'date' => strtotime($day),
                'driver_count' => $dayData['driver_count'],
                'amount' => $dayData['amount'],
                'activated_count' => $operations['activated_count'],
                'deactivated_count' => $operations['deactivated_count'],
                'users' => $operations['users'],
            ];
        }

        return collect($billingData);
    }

    public function sendPdfInvoice(?Invoice $invoice): void
    {
        if ($invoice === null) {
            return;
        }

        $generatePdfService = resolve(GeneratePdfService::class);
        $pdf = $generatePdfService->template2pdf(
            'pdf.billing.invoice',
            [
                'invoice' => $invoice,
            ],
            false
        );

        Notification::route('mail', $invoice->company->getPaymentContactData()['email'])
            ->notify(new SendPdfInvoice($invoice, $pdf));
    }

    /**
     * @param Company $company
     * @param User $user
     * @throws CompanyIsSubscription
     * @throws CompanyPaymentMethodRequired
     */
    public function subscribe(Company $company, User $user): void
    {
        if (!$company->hasPaymentMethod() || $company->hasUnpaidInvoices()) {
            throw new CompanyPaymentMethodRequired();
        }

        if ($company->hasSubscription() && $company->isSubscriptionActive()) {
            throw new CompanyIsSubscription();
        }

        if ($company->hasSubscription() && !$company->isSubscriptionActive()) {
            $company->renewSubscription();

            Notification::route('mail', $company->getPaymentContactData()['email'])
                ->notify(new RenewSubscribe($company));

            EventService::billing($company)
                ->user($user)
                ->update()
                ->broadcast();
        }

        if (!$company->hasSubscription()) {
            $company->createSubscription(
                config('pricing.plans.regular.slug')
            );

            EventService::billing($company)
                ->user($user)
                ->update()
                ->broadcast();
        }
    }

    public function calculateDriverDailyPrice(Company $company): float
    {
        $subscription = $company->subscription;

        if (!$subscription->billing_start || !$subscription->billing_end) {
            return 0;
        }

        if ($company->paymentAttemptsCountExhausted()) {
            return 0;
        }

        $pricePerDriver = $subscription->pricingPlan->price_per_driver;

        $start = $subscription->billing_start;
        $end = $subscription->billing_end;
        $daysInInterval = $start->daysUntil($end)->count();

        if ($daysInInterval) {
            return $pricePerDriver / $daysInInterval;
        }

        return 0;
    }

    /**
     * @param Company|null $company
     * @return bool
     */
    public function checkSubscription(?Company $company): bool
    {
        if ($company === null) {
            return false;
        }

        if (!$company->hasSubscription()) {
            return false;
        }

        if (!$company->isSubscriptionActive()) {
            return false;
        }

        if ($company->hasUnpaidInvoices()) {
            return false;
        }

        return true;
    }

    /**
     * @param Invoice $invoice
     * @throws Exception
     */
    public function chargeInvoice(Invoice $invoice): void
    {
        $paymentService = $this->getPaymentService();

        if ($invoice->pending) {
            throw new Exception(trans(TransactionUnderReviewException::MESSAGE));
        }

        try {
            if ($invoice->amount > 0) {
                $transId = $paymentService->makePayment(
                    $invoice->company->getPaymentMethodData(),
                    $invoice->amount
                );
            }

            $invoice->attempt += 1;
            $invoice->is_paid = true;
            $invoice->paid_at = now()->timestamp;
            $invoice->trans_id = $transId ?? null;

            $attempt_history = [
                [
                    'time' => now()->timestamp,
                    'status' => 'Success',
                    'reason' => '',
                ]
            ];

            $invoice->attempt_history = is_array($invoice->attempt_history)
                ? array_merge($invoice->attempt_history, $attempt_history)
                : $attempt_history;

            $invoice->save();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function markInvoicePaymentFailed(Invoice $invoice, string $errorMessage): void
    {
        $invoice->attempt += 1;
        $invoice->last_attempt_time = now()->timestamp;
        $invoice->last_attempt_response = $errorMessage;
        $invoice->next_attempt_time = now()->addDay()->timestamp;

        $attempt_history = [
            [
                'time' => $invoice->last_attempt_time,
                'status' => 'Failure',
                'reason' => $invoice->last_attempt_response,
            ]
        ];

        $invoice->attempt_history = is_array($invoice->attempt_history)
            ? array_merge($invoice->attempt_history, $attempt_history)
            : $attempt_history;

        $invoice->save();
//dd($invoice);
        if(
            $invoice->attempt >= config('billing.invoices.max_charge_attempts')
            && $invoice->company->isGPSEnabled()
        ){
            /** @var $service DeviceSubscriptionService */
            $service = resolve(DeviceSubscriptionService::class);
            $service->cancelFromUnpaidSubscription($invoice->company->gpsDeviceSubscription);
        }
    }

    public function markInvoicePaymentPending(Invoice $invoice, string $transID): void
    {
        $invoice->pending = true;
        $invoice->trans_id = $transID;
        $invoice->last_attempt_time = now()->timestamp;
        $invoice->last_attempt_response = 'Transaction #' . $transID . ' is under review. Please contact support.';
        $invoice->next_attempt_time = now()->addDay()->timestamp;

        $attempt_history = [
            [
                'time' => $invoice->last_attempt_time,
                'status' => 'Pending',
                'reason' => $invoice->last_attempt_response,
            ]
        ];

        $invoice->attempt_history = is_array($invoice->attempt_history)
            ? array_merge($invoice->attempt_history, $attempt_history)
            : $attempt_history;

        $invoice->save();
    }

    public function markInvoicePaymentPaid(Invoice $invoice): void
    {
        $invoice->attempt += 1;
        $invoice->is_paid = true;
        $invoice->pending = false;
        $invoice->paid_at = now()->timestamp;

        $attempt_history = [
            [
                'time' => now()->timestamp,
                'status' => 'Success',
                'reason' => '',
            ]
        ];

        $invoice->attempt_history = is_array($invoice->attempt_history)
            ? array_merge($invoice->attempt_history, $attempt_history)
            : $attempt_history;

        $invoice->save();
    }

    public function markInvoicePaymentNotPaid(Invoice $invoice): void
    {
        $invoice->attempt += 1;
        $invoice->is_paid = false;
        $invoice->pending = false;
        $invoice->last_attempt_time = now()->timestamp;
        $invoice->last_attempt_response = trans('Payment transaction declined.');
        $invoice->next_attempt_time = now()->addDay()->timestamp;

        $attempt_history = [
            [
                'time' => $invoice->last_attempt_time,
                'status' => 'Failure',
                'reason' => $invoice->last_attempt_response,
            ]
        ];

        $invoice->attempt_history = is_array($invoice->attempt_history)
            ? array_merge($invoice->attempt_history, $attempt_history)
            : $attempt_history;

        $invoice->save();
    }

    /**
     * @param Company $company
     * @param User $user
     * @throws Throwable
     */
    public function unsubscribe(Company $company, User $user): void
    {
        if (!$this->checkSubscription($company)) {
            return;
        }

        $paymentService = $this->getPaymentService();
        $pdf = null;

        try {
            DB::beginTransaction();

            if (!$company->isInTrialPeriod()) {
                logger_info("CREATE INVOICE AS unsubscribe");

                $invoice = $this->createInvoice(
                    $company,
                    $company->subscription->billing_start,
                    now()
                );

                $this->chargeInvoice($invoice);

                $generatePdfService = resolve(GeneratePdfService::class);
                $pdf = $generatePdfService->template2pdf(
                    'pdf.billing.invoice',
                    [
                        'invoice' => $invoice,
                    ],
                    false
                );
            }

            if ($company->hasPaymentContact()) {
                $company->paymentContact->delete();
            }

            if ($company->hasPaymentMethod()) {
                $paymentService->deleteByStoredPaymentData(
                    $company->getPaymentMethodData()
                );

                $company->paymentMethod->delete();
            } else {
                $paymentService->deleteByUserData(
                    $user->id,
                    $user->email
                );
            }

            $company->subscription->canceled = true;

            $company->subscription->save();
            $this->clearCompanyDriverHistory($company->id);

            if($company->isGPSEnabled()){
                $this->deviceSubscriptionService->cancelIfHaulUnsubscribe($company->gpsDeviceSubscription);
            } else {
                // если gps подписка отменены, но есть запрос на активацию девайса, который еще не потвердили
                if($company->gpsDeviceSubscription){
                    $this->deviceSubscriptionService->deviceCancelSubscription($company->gpsDeviceSubscription);
                }

                /** @var $deviceRequestService DeviceRequestService */
                $deviceRequestService = resolve(DeviceRequestService::class);
                $deviceRequestService->closedIfUnsubscribe($company);
            }

            DB::commit();
        } catch (TransactionUnderReviewException $e) {
            DB::rollBack();

            Notification::route(
                'mail',
                $this->getBackofficeService()->getSuperAdmin()->email
            )->notify(
                new InvoicePaymentPending(
                    $e->getTransID()
                )
            );

            throw $e;
        } catch (Exception $e) {
            DB::rollBack();

            Log::error($e);

            throw $e;
        }

        EventService::billing($company)
            ->user($user)
            ->update()
            ->broadcast();

        Notification::route('mail', $company->getPaymentContactData()['email'])
            ->notify(new SuccessUnsubscribe($company, $pdf));
    }
}
