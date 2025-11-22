<?php


namespace App\Http\Controllers\V1\CarrierMobile\Billing;


use App\Http\Controllers\ApiController;
use App\Http\Resources\Billing\SubscriptionInfoMobileResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class SubscriptionController extends ApiController
{
    /**
     * @throws AuthorizationException
     *
     * @OA\Get(
     *     path="/v1/carrier-mobile/subscription-info",
     *     tags={"V1 Carrier-Mobile Subscription"},
     *     summary="Get subscription info for mobile",
     *     operationId="Get subscription info for mobile",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/SubscriptionInfoMobileResource")
     *     ),
     * )
     */
    public function subscriptionInfo(Request $request): SubscriptionInfoMobileResource
    {
        $this->authorize('profile');

        $user = $request->user();

        return SubscriptionInfoMobileResource::make($user->getCompany());
    }
}
