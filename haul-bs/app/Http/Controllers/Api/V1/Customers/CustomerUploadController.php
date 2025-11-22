<?php

namespace App\Http\Controllers\Api\V1\Customers;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Common\SingleAttachmentRequest;
use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Http\Resources\Customers\CustomerResource;
use App\Models\Customers\Customer;
use App\Repositories\Customers\CustomerRepository;
use App\Services\Customers\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CustomerUploadController extends ApiController
{
    public function __construct(
        protected CustomerRepository $repo,
        protected CustomerService $service,
    )
    {}

    /**
     * @OA\Post(
     *     path="/api/v1/customers/{id}/attachments",
     *     tags={"Customers"},
     *     security={{"Basic": {}}},
     *     summary="Add single attachment to customer",
     *     operationId="AddSingleAttachmentToCustomer",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Parameter(name="attachment", in="query", description="attachment file", required=false,
     *         @OA\Schema(type="file",)
     *     ),
     *
     *     @OA\Response(response=200, description="Customer data",
     *         @OA\JsonContent(ref="#/components/schemas/CustomerResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function upload(SingleAttachmentRequest $request, $id): CustomerResource
    {
        $this->authorize(Permission\VehicleOwner\VehicleOwnerUpdatePermission::KEY);

        /** @var $model Customer */
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.customer.not_found")
        );

        return CustomerResource::make(
            $this->service->uploadFile($model, $request->attachment)
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/customers/{id}/attachments/{attachmentId}",
     *     tags={"Customers"},
     *     security={{"Basic": {}}},
     *     summary="Delete attachment from customer",
     *     operationId="DeleteAttachmentFromCustomer",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Parameter(name="{attachmentId}", in="path", required=true, description="ID attachment entity",
     *          @OA\Schema(type="integer", example=1)
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
    public function delete($id, $attachmentId): JsonResponse
    {
        $this->authorize(Permission\VehicleOwner\VehicleOwnerUpdatePermission::KEY);

        /** @var $model Customer */
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.customer.not_found")
        );

        $this->service->deleteFile($model, $attachmentId);

        return $this->successJsonMessage(null, Response::HTTP_NO_CONTENT);
    }
}
