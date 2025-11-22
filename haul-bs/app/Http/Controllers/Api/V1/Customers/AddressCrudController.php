<?php

namespace App\Http\Controllers\Api\V1\Customers;

use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Customers\CustomerAddressRequest;
use App\Http\Resources\Customers\CustomerResource;
use App\Models\Customers\Address;
use App\Repositories\Customers\CustomerAddressRepository;
use App\Repositories\Customers\CustomerRepository;
use App\Services\Customers\CustomerAddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AddressCrudController extends ApiController
{
    public function __construct(
        protected CustomerRepository $customerRepo,
        protected CustomerAddressRepository $repo,
        protected CustomerAddressService $service,
    )
    {}

    /**
     * @OA\Post(
     *     path="/api/v1/customers/{id}/addresses",
     *     tags={"Customers"},
     *     security={{"Basic": {}}},
     *     summary="Add delivery address to customer",
     *     operationId="AddDeliveryAddressToCustomer",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CustomerAddressRequest")
     *     ),
     *
     *     @OA\Response(response=201, description="Customer data",
     *         @OA\JsonContent(ref="#/components/schemas/CustomerResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function store(CustomerAddressRequest $request, $id): CustomerResource|JsonResponse
    {
        $this->authorize(Permission\VehicleOwner\VehicleOwnerUpdatePermission::KEY);

        $customer = $this->customerRepo->getById($id);

        if($customer->addresses->count() >= Address::MAX_COUNT){
            return $this->errorJsonMessage(
                __('exceptions.customer.address.more_limit'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $this->service->create($request->getDto(), $customer);

        return CustomerResource::make($customer->refresh());
    }

    /**
     * @OA\Post(
     *     path="/api/v1/customers/{id}/addresses/{addressId}",
     *     tags={"Customers"},
     *     security={{"Basic": {}}},
     *     summary="Update delivery address to customer",
     *     operationId="UpdateDeliveryAddressToCustomer",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *     @OA\Parameter(name="{addressId}", in="path", required=true, description="ID address entity",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CustomerAddressRequest")
     *     ),
     *
     *     @OA\Response(response=201, description="Customer data",
     *         @OA\JsonContent(ref="#/components/schemas/CustomerResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function update(CustomerAddressRequest $request, $id, $addressId): CustomerResource|JsonResponse
    {
        $this->authorize(Permission\VehicleOwner\VehicleOwnerUpdatePermission::KEY);

        $customer = $this->customerRepo->getById($id);

        /** @var $model Address */
        $model = $this->repo->getById($addressId);

        if($model->fromEcomm()){
            return $this->errorJsonMessage(
                __('exceptions.customer.address.cant_edit_ecomm_address'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $this->service->update($model, $request->getDto());

        return CustomerResource::make($customer->refresh());
    }


    /**
     * @OA\Delete(
     *     path="/api/v1/customers/{id}/addresses/{addressId}",
     *     tags={"Customers"},
     *     security={{"Basic": {}}},
     *     summary="Delete delivery address from customer",
     *     operationId="DeleteDeliveryAddressFromCustomer",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *     @OA\Parameter(name="{addressId}", in="path", required=true, description="ID address entity",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Response(response=204, description="Successful delete"),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function delete($id, $addressId): JsonResponse
    {
        $this->authorize(Permission\VehicleOwner\VehicleOwnerUpdatePermission::KEY);

        /** @var $model Address */
        $model = $this->repo->getById($addressId);

        if($model->fromEcomm()){
            return $this->errorJsonMessage(
                __('exceptions.customer.address.cant_delete_ecomm_address'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $this->service->delete($model);

        return $this->successJsonMessage(null, Response::HTTP_NO_CONTENT);
    }
}
