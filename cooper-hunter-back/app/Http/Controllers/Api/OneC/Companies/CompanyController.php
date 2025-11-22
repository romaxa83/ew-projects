<?php

namespace App\Http\Controllers\Api\OneC\Companies;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\OneC\Companies\AddPriceRequest;
use App\Http\Requests\Api\OneC\Companies\ApproveRequest;
use App\Http\Requests\Api\OneC\Companies\UpdateRequest;
use App\Http\Resources\Api\OneC\Companies\ShippingAddressResource;
use App\Models\Companies\Company;
use App\Repositories\Companies\CompanyRepository;
use App\Services\Companies\CompanyService;
use Illuminate\Http\JsonResponse;
use Throwable;

/**
 * @group Company
 */
class CompanyController extends ApiController
{
    public function __construct(
        protected CompanyRepository $repo,
        protected CompanyService $service
    )
    {}

    /**
     * Shipping address list by company
     *
     * @responseFile 200 docs/api/companies/addresses/list.json
     * @responseFile 500 docs/api/error.json
     *
     * @throws Throwable
     */
    public function shippingAddressList($guid)
    {
        try {
            /** @var $model Company */
            $model = $this->repo->getBy(
                'guid', $guid, [], true, __('exceptions.company.not found by guid' , ['guid' => $guid])
            );

            return ShippingAddressResource::collection($model->shippingAddresses);
        } catch (\Throwable $e) {
            return self::responseError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Approve company
     *
     * @responseFile 200 docs/api/success.json
     * @responseFile 500 docs/api/error.json
     *
     * @throws Throwable
     */
    public function approve(ApproveRequest $request, $guid): JsonResponse
    {
        try {
            /** @var $model Company */
            $model = $this->repo->getBy(
                'guid', $guid, [], true, __('exceptions.company.not found by guid' , ['guid' => $guid])
            );

            makeTransaction(
                fn (): Company => $this->service->approveCompany($model, $request['authorization_code'])
            );

            return self::responseSuccess("Done");
        } catch (\Throwable $e) {
            return self::responseError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Update company
     *
     * @responseFile 200 docs/api/success.json
     * @responseFile 500 docs/api/error.json
     *
     * @throws Throwable
     */
    public function update(UpdateRequest $request, $guid): JsonResponse
    {
        try {
            /** @var $model Company */
            $model = $this->repo->getBy(
                'guid', $guid, [], true, __('exceptions.company.not found by guid' , ['guid' => $guid])
            );

            makeTransaction(
                fn (): Company => $this->service->updateOnec($model, $request->validated())
            );

            return self::responseSuccess("Done");
        } catch (\Throwable $e) {
            return self::responseError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Add prices company
     *
     * @responseFile 200 docs/api/success.json
     * @responseFile 500 docs/api/error.json
     *
     * @throws Throwable
     */
    public function addPrices(AddPriceRequest $request, $guid): JsonResponse
    {
        try {
            /** @var $model Company */
            $model = $this->repo->getBy(
                'guid', $guid, [], true, __('exceptions.company.not found by guid' , ['guid' => $guid])
            );

            $result = makeTransaction(
                fn (): array => $this->service->addPrice($model, $request['data'])
            );

            return self::responseSuccess($result);
        } catch (\Throwable $e) {
            return self::responseError($e->getMessage(), $e->getCode());
        }
    }
}
