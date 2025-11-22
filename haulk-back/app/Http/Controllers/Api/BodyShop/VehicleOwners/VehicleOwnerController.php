<?php

namespace App\Http\Controllers\Api\BodyShop\VehicleOwners;

use App\Exceptions\HasRelatedEntitiesException;
use App\Http\Controllers\ApiController;
use App\Http\Requests\BodyShop\VehicleOwners\VehicleOwnerIndexRequest;
use App\Http\Requests\BodyShop\VehicleOwners\VehicleOwnerRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\Users\SingleAttachmentRequest;
use App\Http\Resources\BodyShop\VehicleOwners\VehicleOwnerPaginateResource;
use App\Http\Resources\BodyShop\VehicleOwners\VehicleOwnerResource;
use App\Http\Resources\BodyShop\VehicleOwners\VehicleOwnerShortListResource;
use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Services\BodyShop\VehicleOwners\VehicleOwnerService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class VehicleOwnerController extends ApiController
{
    private VehicleOwnerService $service;

    public function __construct(VehicleOwnerService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * @param VehicleOwnerIndexRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/body-shop/vehicle-owners",
     *     tags={"Vehicle Owners Body Shop"},
     *     summary="Get vehicle owners paginated list",
     *     operationId="Get vehicle owners data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false,
     *          @OA\Schema( type="integer", default="5")
     *     ),
     *     @OA\Parameter(name="per_page", in="query", description="States per page", required=false,
     *          @OA\Schema( type="integer", default="10")
     *     ),
     *     @OA\Parameter(name="q", in="query", description="Scope for filter by name, email, phone", required=false,
     *          @OA\Schema( type="string", default="name",)
     *     ),
     *     @OA\Parameter(name="tag_id", in="query", description="Tag id", required=false,
     *          @OA\Schema( type="integer", default="1",)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/VehicleOwnerPaginate")
     *     ),
     * )
     */
    public function index(VehicleOwnerIndexRequest $request): AnonymousResourceCollection
    {
        $this->authorize('vehicle-owners');

        $owners = VehicleOwner::query()
            ->filter($request->validated())
            ->orderBy('id', 'desc')
            ->paginate($request->per_page);

        return VehicleOwnerPaginateResource::collection($owners);
    }

    /**
     * @param VehicleOwnerRequest $request
     * @return VehicleOwnerResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     * @OA\Post(path="/api/body-shop/vehicle-owners", tags={"Vehicle Owners Body Shop"}, summary="Create Vehicle Owner", operationId="Create Vehicle Owner", deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="first_name", in="query", description="Vehicle Owner first name", required=true,
     *          @OA\Schema(type="string", default="Mike",)
     *     ),
     *     @OA\Parameter(name="last_name", in="query", description="Vehicle Owner last name", required=true,
     *          @OA\Schema(type="string", default="Stone",)
     *     ),
     *     @OA\Parameter(name="email", in="query", description="Vehicle Owner email", required=true,
     *          @OA\Schema(type="string", default="test@wezom.com.ua",)
     *     ),
     *     @OA\Parameter(name="phone", in="query", description="Vehicle Owner phone", required=true,
     *          @OA\Schema(type="string", default="1234567",)
     *     ),
     *     @OA\Parameter(name="phone_extension", in="query", description="Vehicle Owner extension", required=false,
     *          @OA\Schema(type="string", default="1234567",)
     *     ),
     *     @OA\Parameter(name="phones", in="query", description="Additional phone", required=false,
     *          @OA\Schema(type="array", description="Vehicle Owner aditional phones",
     *              @OA\Items(ref="#/components/schemas/PhonesRaw")
     *          )
     *     ),
     *     @OA\Parameter(name="notes", in="query", description="Notes", required=false,
     *          @OA\Schema(type="string",)
     *     ),
     *     @OA\Parameter(name="attachment_files", in="query", description="Attachments list", required=false, ),
     *     @OA\Parameter(name="tags", in="query", description="Tags list", required=false,
     *          @OA\Schema(type="array",
     *              @OA\Items(anyOf={@OA\Schema(type="integer")})
     *          )
     *     ),
     *     @OA\Response(response=201, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/VehicleOwner")
     *     ),
     * )
     *
     */
    public function store(VehicleOwnerRequest $request)
    {
        $this->authorize('vehicle-owners create');

        try {
            $user = $this->service->create($request->getDto());

            return VehicleOwnerResource::make($user);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->makeErrorResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/body-shop/vehicle-owners/{vehicleOwnerId}",
     *     tags={"Vehicle Owners Body Shop"},
     *     summary="Get vehicle owner record",
     *     operationId="Get vehicle owner record",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Vehicle Owner id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/VehicleOwner")
     *     ),
     * )
     *
     * @param VehicleOwner $vehicleOwner
     * @return VehicleOwnerResource
     * @throws AuthorizationException
     */
    public function show(VehicleOwner $vehicleOwner): VehicleOwnerResource
    {
        $this->authorize('vehicle-owners read');

        return VehicleOwnerResource::make($vehicleOwner);
    }

    /**
     * @OA\Post(
     *     path="/api/body-shop/vehicle-owners/{vehicleOwnerId}",
     *     tags={"Vehicle Owners Body Shop"},
     *     summary="Update vehicle owner record",
     *     operationId="Update vehicle owner",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Vehicle owner id", required=true,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="first_name", in="query", description="Vehicle Owner first name", required=true,
     *          @OA\Schema(type="string", default="Mike",)
     *     ),
     *     @OA\Parameter(name="last_name", in="query", description="Vehicle Owner last name", required=true,
     *          @OA\Schema(type="string", default="Stone",)
     *     ),
     *     @OA\Parameter(name="email", in="query", description="Vehicle Owner email", required=true,
     *          @OA\Schema(type="string", default="test@wezom.com.ua",)
     *     ),
     *     @OA\Parameter(name="phone", in="query", description="Vehicle Owner phone", required=true,
     *          @OA\Schema(type="string", default="1234567",)
     *     ),
     *     @OA\Parameter(name="phone_extension", in="query", description="Vehicle Owner extension", required=false,
     *          @OA\Schema(type="string", default="1234567",)
     *     ),
     *     @OA\Parameter(name="phones", in="query", description="Additional phone", required=false,
     *          @OA\Schema(type="array", description="Vehicle Owner aditional phones",
     *              @OA\Items(ref="#/components/schemas/PhonesRaw")
     *          )
     *     ),
     *     @OA\Parameter(name="notes", in="query", description="Notes", required=false,
     *          @OA\Schema(type="string",)
     *     ),
     *     @OA\Parameter(name="attachment_files", in="query", description="Attachments list", required=false, ),
     *     @OA\Parameter(name="tags", in="query", description="Tags list", required=false,
     *          @OA\Schema(type="array",
     *              @OA\Items(anyOf={@OA\Schema(type="integer")})
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/VehicleOwner")
     *     ),
     * )
     * @param VehicleOwnerRequest $request
     * @param VehicleOwner $vehicleOwner
     * @return VehicleOwnerResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function update(VehicleOwnerRequest $request, VehicleOwner $vehicleOwner)
    {
        $this->authorize('vehicle-owners update');

        try {
            $this->service->update($vehicleOwner, $request->getDto());

            return VehicleOwnerResource::make($vehicleOwner);
        } catch (Exception $e) {
            Log::error($e);

            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/body-shop/vehicle-owners/{vehicleOwnerId}",
     *     tags={"Vehicle Owners Body Shop"},
     *     summary="Delete vehicle owner",
     *     operationId="Delete vehicle owner",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Vehicle owner id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              default="1",
     *          )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation",
     *     ),
     * )
     *
     * @param VehicleOwner $vehicleOwner
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function destroy(VehicleOwner $vehicleOwner)
    {
        $this->authorize('vehicle-owners delete');

        try {
            $this->service->destroy($vehicleOwner);

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (HasRelatedEntitiesException $exception) {
            if ($vehicleOwner->trucks()->exists() && $vehicleOwner->trailers()->exists()) {
                return $this->makeErrorResponse(
                    trans(
                        'This customer has <a href=":trucks">trucks</a> and <a href=":trailers">trailers</a> assigned.',
                        [
                            'trucks' => str_replace('{id}', $vehicleOwner->id, config('frontend.bs_trucks_with_customer_filter_url')),
                            'trailers' => str_replace('{id}', $vehicleOwner->id, config('frontend.bs_trailers_with_customer_filter_url')),
                        ],
                    ),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            if ($vehicleOwner->trucks()->exists()) {
                return $this->makeErrorResponse(
                    trans(
                        'This customer has <a href=":trucks">trucks</a> assigned.',
                        [
                            'trucks' => str_replace('{id}', $vehicleOwner->id, config('frontend.bs_trucks_with_customer_filter_url')),
                        ],
                    ),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            if ($vehicleOwner->trucks()->exists() && $vehicleOwner->trailers()->exists()) {
                return $this->makeErrorResponse(
                    trans(
                        'This customer has <a href=":trailers">trailers</a> assigned.',
                        [
                            'trailers' => str_replace('{id}', $vehicleOwner->id, config('frontend.bs_trailers_with_customer_filter_url')),
                        ],
                    ),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
        } catch (Exception $e) {
            Log::error($e);

            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/body-shop/vehicle-owners/{vehicleOwnerId}/attachments",
     *     tags={"Vehicle Owners Body Shop"},
     *     summary="Add single attachment to vehicle owher",
     *     operationId="Add attachment",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="attachment", in="query", description="attachment file", required=false,
     *          @OA\Schema(type="file",)
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/VehicleOwner")
     *     ),
     * )
     *
     * @param SingleAttachmentRequest $request
     * @param VehicleOwner $vehicleOwner
     * @return JsonResponse|VehicleOwnerResource
     * @throws AuthorizationException
     */
    public function addAttachment(SingleAttachmentRequest $request, VehicleOwner $vehicleOwner)
    {
        $this->authorize('vehicle-owners update');

        if ($vehicleOwner->getCompanyId() !== request()->user()->getCompanyId()) {
            return $this->makeErrorResponse('', Response::HTTP_FORBIDDEN);
        }

        try {
            return new VehicleOwnerResource(
                $this->service->addAttachment(
                    $vehicleOwner,
                    $request->attachment
                )
            );
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());

            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *
     * @OA\Delete(
     *     path="/api/body-shop/vehicle-owners/{vehicleOwnerId}/attachments/{attachmentId}",
     *     tags={"Vehicle Owners Body Shop"},
     *     summary="Delete attachment from vehicle owner",
     *     operationId="Delete attachment",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=204, description="Successful operation"),
     * )
     */
    public function deleteAttachment(VehicleOwner $vehicleOwner, int $id)
    {
        $this->authorize('vehicle-owners update');

        if ($vehicleOwner->getCompanyId() !== request()->user()->getCompanyId()) {
            return $this->makeErrorResponse('', Response::HTTP_FORBIDDEN);
        }

        try {
            $this->service->deleteAttachment($vehicleOwner, $id);

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param SearchRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/body-shop/vehicle-owners/shortlist",
     *     tags={"Vehicle Owners Body Shop"},
     *     summary="Get Vehicle Owners short list",
     *     operationId="Get Vehicle Owners data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="q", in="query", description="Scope for filter by name, email, phone", required=false,
     *          @OA\Schema( type="string", default="name",)
     *     ),
     *     @OA\Parameter(name="searchid", in="query", description="Filter by id", required=false,
     *          @OA\Schema( type="integer", default="1",)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/VehicleOwnerShortList"),
     *     )
     * )
     *
     * @return JsonResponse
     *
     * @throws AuthorizationException
     */
    public function shortlist(SearchRequest $request): AnonymousResourceCollection
    {
        $vehicleOwners = VehicleOwner::query()
            ->filter($request->validated())
            ->limit(SearchRequest::DEFAULT_LIMIT)
            ->get();

        return VehicleOwnerShortListResource::collection($vehicleOwners);
    }
}
