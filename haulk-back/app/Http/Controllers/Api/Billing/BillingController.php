<?php


namespace App\Http\Controllers\Api\Billing;


use App\Exceptions\Billing\CompanyIsSubscription;
use App\Exceptions\Billing\CompanyPaymentMethodRequired;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Billing\PaymentContactRequest;
use App\Http\Requests\Billing\PaymentMethodRequest;
use App\Http\Requests\Billing\UnsubscribeRequest;
use App\Http\Resources\Billing\BillingInfoResource;
use App\Http\Resources\Billing\SubscriptionInfoResource;
use App\Models\Saas\Company\Company;
use App\Models\Users\User;
use App\Notifications\Billing\RenewSubscribe;
use App\Notifications\Saas\Companies\Login\LoginWithFreeTrial;
use App\Notifications\Saas\Companies\Payment\AddPaymentCard;
use App\Notifications\Saas\Companies\Payment\ProblemWithAddPaymentCard;
use App\Services\Billing\BillingService;
use App\Services\Carriers\CarrierService;
use App\Services\Events\EventService;
use App\Services\Permissions\Payments\PaymentProviderInterface;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Throwable;

class BillingController extends ApiController
{
    /**
     * @param Request $request
     * @return SubscriptionInfoResource
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/billing/subscription-info",
     *     tags={"Billing"},
     *     summary="Get subscription info",
     *     operationId="Get subscription info",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/SubscriptionInfoResource")
     *     ),
     * )
     */
    public function subscriptionInfo(Request $request): SubscriptionInfoResource
    {
        $this->authorize('profile');

        $company = $request->user()->getCompany();

        return SubscriptionInfoResource::make(
            $company
        );
    }

    /**
     * @param Request $request
     * @param BillingService $billingService
     * @return BillingInfoResource
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/billing/info",
     *     tags={"Billing"},
     *     summary="Get billing info",
     *     operationId="Get billing info",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/BillingInfoResource")
     *     ),
     * )
     */
    public function billingInfo(Request $request, BillingService $billingService): BillingInfoResource
    {
        $this->authorize('billing');

        $company = $request->user()->getCompany();

        return BillingInfoResource::make(
            [
                'billing_contact' => $company->paymentContact ? [
                    'full_name' => $company->paymentContact->full_name,
                    'email' => $company->paymentContact->email,
                    'use_accounting_contact' => $company->paymentContact->use_accounting_contact,
                ] : null,
                'payment_history' => $company->invoices
                    ? $company->invoices()
                        ->latest()
                        ->take(2)
                        ->get()
                        ->map(
                            function ($el) {
                                return [
                                    'id' => $el->id,
                                    'date' => $el->paid_at,
                                    'amount' => (double) $el->amount,
                                ];
                            }
                        )
                    : [],
                'estimated_payment' => $billingService->calculateEstimatedPayment($company),
                'payment_method' => $company->hasPaymentMethod() ? [
                    'full_name' => $company->getPaymentMethodData()->getBillingName(),
                    'card_number' => $company->getPaymentMethodData()->getCardNumber(),
                    'expires_at' => $company->getPaymentMethodData()->getCardDate(),
                ] : null,
            ]
        );
    }

    /**
     * @param PaymentMethodRequest $request
     * @param PaymentProviderInterface $paymentService
     * @param BillingService $billingService
     * @return JsonResponse
     * @throws AuthorizationException
     * @OA\Post(
     *     path="/api/billing/payment-method",
     *     tags={"Billing"},
     *     summary="Add/update payment info",
     *     operationId="Add/update payment info",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\Parameter(name="first_name", in="query", required=true,
     *         @OA\Schema(type="string",)
     *     ),
     *     @OA\Parameter(name="last_name", in="query", required=true,
     *         @OA\Schema(type="string",)
     *     ),
     *     @OA\Parameter(name="address", in="query", required=true,
     *         @OA\Schema(type="string",)
     *     ),
     *     @OA\Parameter(name="city", in="query", required=true,
     *         @OA\Schema(type="string",)
     *     ),
     *     @OA\Parameter(name="state_id", in="query", required=true,
     *         @OA\Schema(type="integer",)
     *     ),
     *     @OA\Parameter(name="zip", in="query", required=true,
     *         @OA\Schema(type="string",)
     *     ),
     *     @OA\Parameter(name="card_number", in="query", required=true,
     *         @OA\Schema(type="string",)
     *     ),
     *     @OA\Parameter(name="expires_at", in="query", description="mm/yy", required=true,
     *         @OA\Schema(type="string",)
     *     ),
     *     @OA\Parameter(name="cvc", in="query", required=true,
     *         @OA\Schema(type="string",)
     *     ),
     *
     *     @OA\Response(response=200, description="Successful operation",),
     * )
     */
    public function updatePaymentMethod(
        PaymentMethodRequest $request,
        PaymentProviderInterface $paymentService,
        BillingService $billingService
    ): JsonResponse
    {
        $this->authorize('billing update');

        /** @var $user User */
        $user = $request->user();
        /** @var $company Company */
        $company = $user->getCompany();

        try {
            if ($company->hasPaymentMethod()) {
                $paymentService->deleteByStoredPaymentData(
                    $company->getPaymentMethodData()
                );
            } else {
                $paymentService->deleteByUserData(
                    $user->id,
                    $user->email
                );
            }

            $dto = $request->getDto();

            $paymentData = $paymentService->storePaymentData($dto);

            if ($company->paymentMethod) {
                $company->paymentMethod->payment_data = $paymentData->getData();
                $company->paymentMethod->save();
            } else {
                $paymentMethod = $company->paymentMethod()->create();
                $paymentMethod->payment_provider = $paymentService->getProviderName();
                $paymentMethod->payment_data = $paymentData->getData();
                $paymentMethod->save();

                $user->notify(new LoginWithFreeTrial());
            }

            if (
                !$company->hasUnpaidInvoices()
                && $company->hasSubscription()
                && !$company->isSubscriptionActive()
            ) {
                $company->renewSubscription();

                Notification::route('mail', $company->getPaymentContactData()['email'])
                    ->notify(new RenewSubscribe($company));
            }

            if ($company->isTrialExpired()) {
                $currentSubscription = $company->subscription;
                $currentSubscription->delete();

                $company->createSubscription(config('pricing.plans.regular.slug'));
            }

            $company->refresh();

            $billingService->trackCompanyActiveDrivers($company);

            EventService::billing($company)
                ->user($request->user())
                ->update()
                ->broadcast();
        } catch (Exception $e) {
            $company->notify(new ProblemWithAddPaymentCard());

            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $company->notify(new AddPaymentCard());

        return $this->makeSuccessResponse(null, Response::HTTP_OK);
    }

    /**
     * @param PaymentContactRequest $request
     * @return JsonResponse
     * @throws AuthorizationException
     * @OA\Post(
     *     path="/api/billing/payment-contact",
     *     tags={"Billing"},
     *     summary="Add/update payment contact",
     *     operationId="Add/update payment contact",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(
     *          name="full_name",
     *          in="query",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="email",
     *          in="query",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     * )
     */
    public function updatePaymentContact(PaymentContactRequest $request): JsonResponse
    {
        $this->authorize('billing update');

        $company = $request->user()->getCompany();

        if ($company->paymentContact) {
            $company->paymentContact->update($request->validated());
        } else {
            $company->paymentContact()->create($request->validated());
        }

        EventService::billing($company)
            ->user($request->user())
            ->update()
            ->broadcast();

        return $this->makeSuccessResponse(null, Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     * @OA\Delete(
     *     path="/api/billing/payment-contact",
     *     tags={"Billing"},
     *     summary="Delete payment contact",
     *     operationId="Delete payment contact",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation",
     *     ),
     * )
     */
    public function deletePaymentContact(Request $request): JsonResponse
    {
        $this->authorize('billing update');

        $company = $request->user()->getCompany();

        $company->paymentContact->delete();

        EventService::billing($company)
            ->user($request->user())
            ->update()
            ->broadcast();

        return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Request $request
     * @param BillingService $billingService
     * @param CarrierService $carrierService
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     * @OA\Put(
     *     path="/api/billing/subscribe",
     *     tags={"Billing"},
     *     summary="Billing subscribe",
     *     operationId="Billing subscribe",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     * )
     */
    public function subscribe(Request $request, BillingService $billingService, CarrierService $carrierService): JsonResponse
    {
        $this->authorize('billing update');

        $company = $request->user()->getCompany();

        try {
            $billingService->subscribe($company, $request->user());
            $billingService->trackCompanyActiveDrivers($company);
        } catch (CompanyPaymentMethodRequired $e) {
            return $this->makeErrorResponse(null, Response::HTTP_PAYMENT_REQUIRED);
        } catch (CompanyIsSubscription $e) {
            return $this->makeErrorResponse(null, Response::HTTP_FORBIDDEN);
        } catch (Exception $e) {
            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $carrierService->declineDestroy($company);

        return $this->makeSuccessResponse(null, Response::HTTP_OK);
    }

    /**
     * @param UnsubscribeRequest $unsubscribeRequest
     * @param BillingService $billingService
     * @return JsonResponse
     * @throws Throwable
     * @OA\Put(
     *     path="/api/billing/unsubscribe",
     *     tags={"Billing"},
     *     summary="Unsubscribtion company",
     *     operationId="Unsubscribtion company",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Response (response=200, description="Successful confirm"),
     *     @OA\Response (response=204, description="Success decline"),
     *     @OA\Response (response=422, description="Validate error"),
     *     @OA\Response (response=402, description="Payment required"),
     *     @OA\Response (response=500, description="Server error"),
     *     @OA\Response (response=403, description="Forbiden")
     * )
     */
    public function unsubscribe(
        UnsubscribeRequest $unsubscribeRequest,
        BillingService $billingService
    ): JsonResponse
    {
        /**@var Company $company*/
        $company = $unsubscribeRequest->validated()['company'];

        try {
            $billingService->unsubscribe($company, $unsubscribeRequest->user());
        } catch (Exception $e) {
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->makeSuccessResponse(null, Response::HTTP_OK);
    }
}
