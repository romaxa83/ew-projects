<?php

namespace App\Http\Middleware;

use App\Models\Users\User;
use App\Services\Billing\BillingService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user(User::GUARD);

        if ($user) {
            $company = $user->getCompany();

            if ($company) {
                /** @var $billingService BillingService */
                $billingService = resolve(BillingService::class);

                if ($billingService->showPaymentRequiredError($company, $request)) {
                    return response(
                        [
                            'errors' => [
                                [
                                    'title' => trans('Credit card info is not valid or missing.'),
                                    'status' => Response::HTTP_PAYMENT_REQUIRED,
                                ]
                            ]
                        ],
                        Response::HTTP_PAYMENT_REQUIRED
                    );
                }
            } else {
                return response(
                    [
                        'errors' => [
                            [
                                'title' => trans('Company not found.'),
                                'status' => Response::HTTP_FORBIDDEN,
                            ]
                        ]
                    ],
                    Response::HTTP_FORBIDDEN
                );
            }
        }

        return $next($request);
    }
}
