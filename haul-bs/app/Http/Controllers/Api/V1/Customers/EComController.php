<?php

namespace App\Http\Controllers\Api\V1\Customers;

use App\Dto\Tags\TagDto;
use App\Enums\Tags\TagType;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Customers\CustomerAddressRequest;
use App\Http\Requests\Customers\CustomerEComFilterRequest;
use App\Http\Requests\Customers\CustomerEComRequest;
use App\Http\Requests\Customers\CustomerTaxExemptionUploadEComRequest;
use App\Http\Resources\Customers\AddressResource;
use App\Http\Resources\Customers\CustomerResource;
use App\Http\Resources\Customers\CustomerShortPaginationResource;
use App\Http\Resources\Customers\CustomerTaxExemptionResource;
use App\Models\Customers\Address;
use App\Models\Customers\Customer;
use App\Models\Tags\Tag;
use App\Repositories\Customers\CustomerAddressRepository;
use App\Repositories\Customers\CustomerRepository;
use App\Repositories\Tags\TagRepository;
use App\Services\Customers\CustomerAddressService;
use App\Services\Customers\CustomerService;
use App\Services\Customers\CustomerTaxExemptionService;
use App\Services\Tags\TagService;
use App\Taps\Customers\NotHaulk;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

class EComController extends ApiController
{
    public function __construct(
        protected CustomerService $service,
        protected CustomerTaxExemptionService $taxExemptionService,
        protected CustomerAddressService $addressService,
        protected CustomerRepository $repo,
        protected CustomerAddressRepository $addressRepo,
        protected TagRepository $tagRepo,
        protected TagService $tagService,
    )
    {}

    /**
     * @OA\Get (
     *     path="/api/v1/e-comm/customers",
     *     tags={"E-Commerce"},
     *     security={{"Basic": {}}},
     *     summary="Get customer paginated list for e-comm",
     *     operationId="GetCustomerPaginatedListForEComm",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization_EComm"),
     *
     *     @OA\Parameter(ref="#/components/parameters/Page"),
     *     @OA\Parameter(ref="#/components/parameters/PerPage"),
     *
     *     @OA\Parameter(name="email", in="query", required=false,
     *          description="Scope for filter by email",
     *          @OA\Schema(type="string", default="null",)
     *      ),
     *
     *     @OA\Response(response=200, description="Customer data",
     *         @OA\JsonContent(ref="#/components/schemas/CustomerShortPaginationResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function index(CustomerEComFilterRequest $request): ResourceCollection
    {
        $models = $this->repo->getAllPagination(
            filters: $request->validated(),
            relation: ['tags']
        );

        return CustomerShortPaginationResource::collection(
            $models
        );
    }

    /**
     * @OA\Post (
     *     path="/api/v1/e-comm/customers",
     *     tags={"E-Commerce"},
     *     security={{"Basic": {}}},
     *     summary="Create customer for e-comm",
     *     operationId="CreateCustomerForEComm",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization_EComm"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CustomerEComRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Customer data",
     *         @OA\JsonContent(ref="#/components/schemas/CustomerResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function store(CustomerEComRequest $request): CustomerResource
    {
        $tag = $this->tagRepo->getEcommTag();
        if(!$tag) {
            $tagDto = TagDto::byArgs([
                'name' =>Tag::ECOM_NAME_TAG,
                'type' => TagType::CUSTOMER,
                'color' => '#006D75',
            ]);
            $tag = $this->tagService->create($tagDto);
        }

        $dto = $request->getDto();
        $dto->tags = [$tag->id];
        /** @var $customer Customer */
        $customer = $this->repo->getBy(['email' => $dto->email->getValue()]);
        if($customer){
            $customer = $this->service->updateFromECom($customer, $dto);
        } else {
            $customer = $this->service->createFromECom($dto);
        }

        return CustomerResource::make($customer);
    }

    /**
     * @OA\Put (
     *     path="/api/v1/e-comm/customers/{id}",
     *     tags={"E-Commerce"},
     *     security={{"Basic": {}}},
     *     summary="Update customer for e-comm",
     *     operationId="UpdateCustomerForEComm",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization_EComm"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CustomerEComRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Customer data",
     *         @OA\JsonContent(ref="#/components/schemas/CustomerResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function update(CustomerEComRequest $request, $id): CustomerResource
    {
        logger_info('UPDATE CUSTOMER ECOM REQUEST', [
            'id' => $id,
            'data' => $request->validated()
        ]);

        /** @var $model Customer */
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.customer.not_found"),
            taps: [new NotHaulk()]
        );

        if(!$model->hasECommAccount()){
            throw new \Exception(__("exceptions.customer.not_found"), Response::HTTP_NOT_FOUND);
        }

        return CustomerResource::make(
            $this->service->updateFromECom($model, $request->getDto())
        );
    }

    /**
     * @OA\Put (
     *     path="/api/v1/e-comm/customers/{id}/set-ecomm-tag",
     *     tags={"E-Commerce"},
     *     security={{"Basic": {}}},
     *     summary="Set a tag for the customer if he doesn’t have it",
     *     operationId="SetECommTagForCustomer",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization_EComm"),
     *
     *     @OA\Response(response=200, description="Customer data",
     *         @OA\JsonContent(ref="#/components/schemas/CustomerResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function setECommTag($id): CustomerResource
    {
        /** @var $model Customer */
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.customer.not_found"),
        );

        if(!$model->hasECommTag()){
            $tag = $this->tagRepo->getEcommTag();
            if(!$tag) {
                $tagDto = TagDto::byArgs([
                    'name' =>Tag::ECOM_NAME_TAG,
                    'type' => TagType::CUSTOMER,
                    'color' => '#006D75',
                ]);
                $tag = $this->tagService->create($tagDto);
            }

            $model->tags()->attach($tag);
        }

        return CustomerResource::make($model);
    }

    /**
     * @OA\Post (
     *     path="/api/v1/e-comm/customers/{id}/addresses",
     *     tags={"E-Commerce"},
     *     security={{"Basic": {}}},
     *     summary="Add delivery addres to customer",
     *     operationId="AddDeliveryAddresToCustomerEComm",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization_EComm"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CustomerAddressRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Customer data",
     *         @OA\JsonContent(ref="#/components/schemas/AddressResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function addAddress(CustomerAddressRequest $request, $id): AddressResource|JsonResponse
    {
        $customer = $this->repo->getById($id);

        if($customer->addresses->count() >= Address::MAX_COUNT){
            return $this->errorJsonMessage(__('exceptions.tag.more_limit'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $dto = $request->getDto();
        $dto->fromEcomm = true;

        return AddressResource::make(
            $this->addressService->create($dto, $customer)
        );
    }

    /**
     * @OA\Put (
     *     path="/api/v1/e-comm/customers/{id}/addresses/{addressId}",
     *     tags={"E-Commerce"},
     *     security={{"Basic": {}}},
     *     summary="Update delivery addres to customer",
     *     operationId="UpdateDeliveryAddresToCustomerEComm",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization_EComm"),
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
     *     @OA\Response(response=200, description="Customer address data",
     *         @OA\JsonContent(ref="#/components/schemas/AddressResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function updateAddress(CustomerAddressRequest $request, $id, $addressId): AddressResource
    {
        $model = $this->addressRepo->getById($addressId);

        return AddressResource::make(
            $this->addressService->update($model, $request->getDto())
        );
    }


    /**
     * @OA\Delete (
     *     path="/api/v1/e-comm/customers/{id}/addresses/{addressId}",
     *     tags={"E-Commerce"},
     *     security={{"Basic": {}}},
     *     summary="Delete delivery addres to customer",
     *     operationId="DeleteDeliveryAddresToCustomerEComm",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization_EComm"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *     @OA\Parameter(name="{addressId}", in="path", required=true, description="ID address entity",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(response=204, description="Successful delete"),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function deleteAddress($id, $addressId): JsonResponse
    {
        $model = $this->addressRepo->getById($addressId);

        $this->addressService->delete($model);

        return $this->successJsonMessage(null, Response::HTTP_NO_CONTENT);
    }


    /**
     * @OA\Put (
     *     path="/api/v1/e-comm/customers/{email}/upload-tax-exemption",
     *     tags={"E-Commerce"},
     *     security={{"Basic": {}}},
     *     summary="Upload customer tax exemption for e-comm",
     *     operationId="UploadTaxExemptionForEComm",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization_EComm"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CustomerTaxExemptionUploadEComRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Customer data",
     *         @OA\JsonContent(ref="#/components/schemas/CustomerResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function uploadTaxExemption(CustomerTaxExemptionUploadEComRequest $request, $email): CustomerResource
    {
        /** @var $model Customer */
        $model = $this->repo->getBy(['email' => $email],
            withException: true,
            exceptionMessage: __("exceptions.customer.not_found"),
            taps: [new NotHaulk()]
        );

        if(!$model->type->isEComm()){
            throw new \Exception(__("exceptions.customer.not_found"), Response::HTTP_NOT_FOUND);
        }

        return CustomerResource::make(
            $this->taxExemptionService->createECom($model, $request->getDto())
        );
    }

    /**
     * @OA\Put (
     *     path="/api/v1/e-comm/customers/{email}/delete-tax-exemption",
     *     tags={"E-Commerce"},
     *     security={{"Basic": {}}},
     *     summary="Delete customer tax exemption for e-comm",
     *     operationId="DeleteTaxExemptionForEComm",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization_EComm"),
     *
     *     @OA\Response(response=200, description="Customer data",
     *         @OA\JsonContent(ref="#/components/schemas/CustomerResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function deleteTaxExemption($email): CustomerResource
    {
        /** @var $model Customer */
        $model = $this->repo->getBy(['email' => $email],
            withException: true,
            exceptionMessage: __("exceptions.customer.not_found"),
            taps: [new NotHaulk()]
        );

        if(!$model->type->isEComm()){
            throw new \Exception(__("exceptions.customer.not_found"), Response::HTTP_NOT_FOUND);
        }

        return CustomerResource::make(
            $this->taxExemptionService->delete($model)->refresh()
        );
    }

    /**
     * @OA\Get (
     *     path="/api/v1/e-comm/customers/{email}/get-tax-exemption",
     *     tags={"E-Commerce"},
     *     security={{"Basic": {}}},
     *     summary="Get customer tax exemption for e-comm",
     *     operationId="GetTaxExemptionForEComm",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization_EComm"),
     *
     *     @OA\Response(response=200, description="Customer Tax Exemption data",
     *         @OA\JsonContent(ref="#/components/schemas/CustomerTaxExemptionResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function getTaxExemption($email): CustomerTaxExemptionResource|JsonResponse
    {
        /** @var $model Customer */
        $model = $this->repo->getBy(['email' => $email],
            withException: true,
            exceptionMessage: __("exceptions.customer.not_found"),
        );

        return $model->taxExemption ? CustomerTaxExemptionResource::make($model->taxExemption) : response()->json(['data' => null]);
    }
}
