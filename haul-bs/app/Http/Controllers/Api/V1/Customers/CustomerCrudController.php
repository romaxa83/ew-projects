<?php

namespace App\Http\Controllers\Api\V1\Customers;

use App\Events\Events\Customers\CustomerGiveEcommTag;
use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Customers\CustomerFilterRequest;
use App\Http\Requests\Customers\CustomerRequest;
use App\Http\Requests\Customers\CustomerShortListRequest;
use App\Http\Resources\Customers\CustomerPaginationResource;
use App\Http\Resources\Customers\CustomerResource;
use App\Http\Resources\Customers\CustomerShortListResource;
use App\Models\Customers\Customer;
use App\Models\Users\User;
use App\Repositories\Customers\CustomerRepository;
use App\Services\Customers\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

class CustomerCrudController extends ApiController
{
    public function __construct(
        protected CustomerRepository $repo,
        protected CustomerService $service,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/customers",
     *     tags={"Customers"},
     *     security={{"Basic": {}}},
     *     summary="Get customers paginated list",
     *     operationId="GetCustomersPaginatedList",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/Page"),
     *     @OA\Parameter(ref="#/components/parameters/PerPage"),
     *
     *     @OA\Parameter(name="search", in="query", required=false,
     *         description="Scope for filter by name, email, phone",
     *         @OA\Schema(type="string", default="null",)
     *     ),
     *     @OA\Parameter(name="tag_id", in="query", description="Tag id", required=false,
     *           @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="types", in="query", description="Customer types", required=false,
     *         @OA\Schema(type="array",
     *             @OA\Items(allOf={@OA\Schema(type="string", enum={"bs", "ecomm"})})
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Paginated data",
     *         @OA\JsonContent(ref="#/components/schemas/CustomerPaginationResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function index(CustomerFilterRequest $request): ResourceCollection
    {
        $this->authorize(Permission\VehicleOwner\VehicleOwnerReadPermission::KEY);

        /** @var $user User */
        $user = $request->user();
        $filter = $request->validated();
        if($user->role->isSalesManager()){
            $filter['for_sales_manager'] = $user->id;
        }

        $models = $this->repo->getAllPagination(
            relation: ['comments'],
            filters: $filter,
//            taps: [new NotHaulk()],
            sort: 'created_at'
        );

        return CustomerPaginationResource::collection($models);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/customers/shortlist",
     *     tags={"Customers"},
     *     security={{"Basic": {}}},
     *     summary="Get Customers short list",
     *     operationId="GetCustomersShortlist",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/ID"),
     *     @OA\Parameter(ref="#/components/parameters/Limit"),
     *
     *     @OA\Parameter(name="search", in="query", description="Scope for filter by name, email, phone", required=false,
     *          @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(response=200, description="Customer data",
     *         @OA\JsonContent(ref="#/components/schemas/CustomerShortListResource"),
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="422", description="Not Found", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function shortlist(CustomerShortListRequest $request): AnonymousResourceCollection
    {
        $this->authorize(Permission\VehicleOwner\VehicleOwnerReadPermission::KEY);

        /** @var $user User */
        $user = $request->user();
        $filter = $request->validated();
        if($user->role->isSalesManager()){
            $filter['for_sales_manager'] = $user->id;
        }

        return CustomerShortListResource::collection(
            $this->repo->getAll(
                filters: $filter,
                limit: $filter['limit'] ?? CustomerShortListRequest::DEFAULT_LIMIT,
//                taps: [new NotHaulk()]
            )
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/customers",
     *     tags={"Customers"},
     *     security={{"Basic": {}}},
     *     summary="Create customer",
     *     operationId="CreateCustomer",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CustomerRequest")
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
    public function store(CustomerRequest $request): CustomerResource
    {
        $this->authorize(Permission\VehicleOwner\VehicleOwnerCreatePermission::KEY);

        $model = $this->service->create($request->getDto());

        if($model->hasECommTag()){
            event(new CustomerGiveEcommTag($model));
        }

        return CustomerResource::make($model);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/customers/{id}",
     *     tags={"Customers"},
     *     security={{"Basic": {}}},
     *     summary="Update customer",
     *     operationId="UpadteCustomers",
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
     *         @OA\JsonContent(ref="#/components/schemas/CustomerRequest")
     *     ),
     *
     *     @OA\Response(response=201, description="Customer data",
     *         @OA\JsonContent(ref="#/components/schemas/CustomerResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function update(CustomerRequest $request, $id): CustomerResource
    {
        $this->authorize(Permission\VehicleOwner\VehicleOwnerUpdatePermission::KEY);

        /** @var $model Customer */
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.customer.not_found"),
//            taps: [new NotHaulk()]
        );
        $oldData = $model->hasECommTag();

        $model = $this->service->update($model, $request->getDto());

        if($oldData == false && $model->hasECommTag()){
            event(new CustomerGiveEcommTag($model));
        }

        return CustomerResource::make($model);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/customers/{id}",
     *     tags={"Customers"},
     *     security={{"Basic": {}}},
     *     summary="Get info about customer",
     *     operationId="GetInfoAboutCustomers",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Response(response=200, description="Supplier data",
     *         @OA\JsonContent(ref="#/components/schemas/CustomerResource")
     *     ),
     *
     *      @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *      @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *      @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function show($id): CustomerResource
    {
        $this->authorize(Permission\VehicleOwner\VehicleOwnerReadPermission::KEY);

        return CustomerResource::make(
            $this->repo->getBy(['id' => $id],
                withException: true,
                exceptionMessage: __("exceptions.customer.not_found"),
//                taps: [new NotHaulk()]
            )
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/customers/{id}",
     *     tags={"Customers"},
     *     security={{"Basic": {}}},
     *     summary="Delete customer",
     *     operationId="DeleteCustomers",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
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
    public function delete($id): JsonResponse
    {
        $this->authorize(Permission\VehicleOwner\VehicleOwnerDeletePermission::KEY);

        /** @var $model Customer */
        $model = $this->repo->getById($id);

        if(
            auth_user()->role->isSalesManager()
            && auth_user()->id != $model->sales_manager_id
        ){
            return $this->errorJsonMessage(
                __('exceptions.customer.cant_delete_not_owner'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if ($model->hasRelatedEntities()) {
            return $this->errorJsonMessage($this->getMessageForDeleteFailed($model),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $this->service->delete($model);

        return $this->successJsonMessage(null, Response::HTTP_NO_CONTENT);
    }

    protected function getMessageForDeleteFailed(Customer $model): string
    {
        if($model->trucks()->exists() && $model->trailers()->exists()){
            $truckLink = str_replace('{id}', $model->id, config('routes.front.trucks_with_customer_filter_url'));
            $trailerLink = str_replace('{id}', $model->id, config('routes.front.trailers_with_customer_filter_url'));

            return __("exceptions.customer.has_truck_and_trailer", [
                'trucks' => $truckLink,
                'trailers' => $trailerLink
            ]);
        }
        if($model->trucks()->exists()){
            $truckLink = str_replace('{id}', $model->id, config('routes.front.trucks_with_customer_filter_url'));

            return __("exceptions.customer.has_truck", [
                'trucks' => $truckLink,
            ]);
        }
        if($model->trailers()->exists()){
            $trailerLink = str_replace('{id}', $model->id, config('routes.front.trailers_with_customer_filter_url'));

            return __("exceptions.customer.has_trailer", [
                'trailers' => $trailerLink
            ]);
        }

        return '';
    }
}
