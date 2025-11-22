<?php

namespace App\Http\Controllers\Api\V1\Delivery;

use App\Dto\Delivery\DeliveryAddressDto;
use App\Dto\Delivery\DeliveryAddressRateDto;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Delivery\AddressEComRequest;
use App\Http\Requests\Delivery\RateEComRequest;
use App\Http\Resources\Delivery\DeliveryRateResource;
use App\Services\DeliveryServices\DeliveryRateGenerator;
use App\Services\DeliveryServices\FedexAddressValidation;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EcomController extends ApiController
{

    /**
     * @OA\Post (
     *     path="/api/v1/e-comm/delivery/get-rate",
     *     tags={"E-Commerce"},
     *     security={{"Basic": {}}},
     *     summary="Get delivery rate",
     *     operationId="GetDeliveryRate",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization_EComm"),
     *
     *     @OA\RequestBody(required=true,
     *          @OA\JsonContent(ref="#/components/schemas/RateEComRequest")
     *      ),
     *     @OA\Response(response=200, description="Comment data",
     *          @OA\JsonContent(ref="#/components/schemas/DeliveryRateListResource")
     *      ),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function rate(RateEComRequest $request): AnonymousResourceCollection
    {
        return DeliveryRateResource::collection(
            app(DeliveryRateGenerator::class)->generate(DeliveryAddressRateDto::byArgs(array_merge($request->validated())))
        );
    }

    /**
     * @OA\Post (
     *     path="/api/v1/e-comm/delivery/address-validate",
     *     tags={"E-Commerce"},
     *     security={{"Basic": {}}},
     *     summary="Address validate",
     *     operationId="AddressValidate",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization_EComm"),
     *
     *     @OA\RequestBody(required=true,
     *          @OA\JsonContent(ref="#/components/schemas/AddressEComRequest")
     *      ),
     *     @OA\Response(response=200, description="Delivery Rate",
     *         @OA\JsonContent(ref="#/components/schemas/DeliveryRate")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function addressValidate(AddressEComRequest $request): DeliveryRateResource
    {
        (new FedexAddressValidation(DeliveryAddressDto::byArgs($request->validated())))->validate();
        return DeliveryRateResource::make(
            DeliveryAddressRateDto::byArgs(array_merge($request->validated()))
        );
    }
}
