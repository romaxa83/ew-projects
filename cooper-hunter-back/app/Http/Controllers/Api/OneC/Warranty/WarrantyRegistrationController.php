<?php

namespace App\Http\Controllers\Api\OneC\Warranty;

use App\Dto\Warranty\WarrantyCreateOnecDto;
use App\Dto\Warranty\WarrantyUpdateOnecDto;
use App\Enums\Projects\Systems\WarrantyStatus;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\OneC\Warranty\WarrantyRegistrationCreateRequest;
use App\Http\Requests\Api\OneC\Warranty\WarrantyRegistrationListRequest;
use App\Http\Requests\Api\OneC\Warranty\WarrantyRegistrationProcessRequest;
use App\Http\Resources\Api\OneC\Warranty\WarrantyRegistrationResource;
use App\Models\Warranty\Deleted\WarrantyRegistrationDeleted;
use App\Models\Warranty\WarrantyRegistration;
use App\Services\Warranty\WarrantyDeletedService;
use App\Services\Warranty\WarrantyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Throwable;

/**
 * @group Warranty registrations
 *
 * @enum App\Enums\Projects\Systems\WarrantyStatus
 * @enum App\Enums\Warranties\WarrantyType
 */
class WarrantyRegistrationController extends ApiController
{
    public function __construct(protected WarrantyService $service)
    {}

    /**
     * List
     *
     * Get list of specific warranty status
     *
     * @permission App\Permissions\Warranty\WarrantyRegistration\WarrantyRegistrationListPermission
     *
     * @responseFile docs/api/warranty/warranty-registrations/list.json
     */
    public function index(WarrantyRegistrationListRequest $request): AnonymousResourceCollection
    {
        return WarrantyRegistrationResource::collection(
            WarrantyRegistration::query()
                ->filter($request->validated())
                ->with('member')
                ->with('unitsPivot.product:id,guid,title')
                ->union(
                    WarrantyRegistrationDeleted::query()
                        ->filter($request->validated())
                        ->with('member')
                        ->with('unitsPivot.product:id,guid,title')
                )
                ->paginate(
                    perPage: $request['per_page'] ?? 15,
                    page: $request['page'] ?? 1
                )
        );
    }

    /**
     * Pending
     *
     * Get list of pending warranty requests
     *
     * @bodyParam per_page int
     * @bodyParam page int
     *
     * @permission warranty_registration.list
     *
     * @responseFile docs/api/warranty/warranty-registrations/list.json
     */
    public function pending(): AnonymousResourceCollection
    {
        return WarrantyRegistrationResource::collection(
            WarrantyRegistration::query()
                ->where('warranty_status', WarrantyStatus::PENDING)
                ->with('member')
                ->with('unitsPivot.product:id,guid,title')
                ->paginate()
        );
    }

    /**
     * Process warranty
     *
     * Manage warranty status
     *
     * @permission warranty_registration.update
     * @responseFile docs/api/warranty/warranty-registrations/single.json
     *
     * @throws Throwable
     */
    public function process(
        WarrantyRegistration $warranty,
        WarrantyRegistrationProcessRequest $request
    ): WarrantyRegistrationResource
    {
        $status = WarrantyStatus::fromValue($request->get('warranty_status'));

        $dto = WarrantyUpdateOnecDto::byArgs($request->all());

        if($status->isDelete()){
            $serviceDeleted = resolve(WarrantyDeletedService::class);
            $model = makeTransaction(static fn() => $serviceDeleted->copy($warranty));

            return WarrantyRegistrationResource::make($model);
        }

        return WarrantyRegistrationResource::make(
            makeTransaction(fn() => $this->service->process(
                $warranty,
                $request->validated()['serial_numbers'],
                $dto
            )));
    }

    /**
     * Create warranty
     *
     * @responseFile docs/api/warranty/warranty-registrations/single.json
     *
     * @throws Throwable
     */
    public function create(
        WarrantyRegistrationCreateRequest $request,
    ): WarrantyRegistrationResource|JsonResponse
    {
        try {
            $dto = WarrantyCreateOnecDto::byArgs($request->all());

            return WarrantyRegistrationResource::make(
                makeTransaction(fn() => $this->service->create(
                    $dto,
                    $request->validated()['serial_numbers'],
                )));
        } catch (\Throwable $e) {
            return self::responseError($e->getMessage(), $e->getCode());
        }
    }
}
