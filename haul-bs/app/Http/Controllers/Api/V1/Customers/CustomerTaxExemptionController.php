<?php

namespace App\Http\Controllers\Api\V1\Customers;

use App\Events\Events\Customers\DeleteCustomerTaxExemptionEvent;
use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Customers\CustomerTaxExemptionAcceptedRequest;
use App\Http\Requests\Customers\CustomerTaxExemptionRequest;
use App\Http\Resources\Customers\CustomerResource;
use App\Models\Customers\Customer;
use App\Services\Customers\CustomerTaxExemptionService;
use Illuminate\Http\JsonResponse;

class CustomerTaxExemptionController extends ApiController
{
    public function __construct(
        protected CustomerTaxExemptionService $service,
    )
    {}

    /**
     * @OA\Post(
     *     path="/api/v1/customers/{customer}/tax-exemption",
     *     tags={"Customers"},
     *     security={{"Basic": {}}},
     *     summary="Create tax-exemption",
     *     operationId="CreateCustomerTaxExemption",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CustomerTaxExemptionRequest")
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
    public function store(Customer $customer, CustomerTaxExemptionRequest $request): CustomerResource
    {
        $this->authorize(Permission\VehicleOwner\VehicleOwnerUpdatePermission::KEY);

        $model = $this->service->create($customer, $request->getDto());

        return CustomerResource::make($model);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/customers/{customer}/tax-exemption/accepted",
     *     tags={"Customers"},
     *     security={{"Basic": {}}},
     *     summary="Accepted tax-exemption",
     *     operationId="AcceptedCustomerTaxExemption",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CustomerTaxExemptionAcceptedRequest")
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
    public function accepted(Customer $customer, CustomerTaxExemptionAcceptedRequest $request): CustomerResource
    {
        $this->authorize(Permission\VehicleOwner\VehicleOwnerUpdatePermission::KEY);

        $model = $this->service->accepted($customer, $request->input('date_active_to'));

        return CustomerResource::make($model);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/customers/{customer}/tax-exemption/decline",
     *     tags={"Customers"},
     *     security={{"Basic": {}}},
     *     summary="Decline tax-exemption",
     *     operationId="DeclineCustomerTaxExemption",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
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
    public function decline(Customer $customer): CustomerResource
    {
        $this->authorize(Permission\VehicleOwner\VehicleOwnerUpdatePermission::KEY);

        $model = $this->service->decline($customer);

        return CustomerResource::make($model);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/customers/{customer}/tax-exemption/delete",
     *     tags={"Customers"},
     *     security={{"Basic": {}}},
     *     summary="Delete tax-exemption",
     *     operationId="DeleteCustomerTaxExemption",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
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
    public function delete(Customer $customer): JsonResponse|CustomerResource
    {
        $this->authorize(Permission\VehicleOwner\VehicleOwnerUpdatePermission::KEY);

        $model = $this->service->delete($customer);

        event(new DeleteCustomerTaxExemptionEvent($customer));

        return CustomerResource::make($model);
    }
}
