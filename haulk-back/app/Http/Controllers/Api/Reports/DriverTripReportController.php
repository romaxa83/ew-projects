<?php

namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Reports\DriverTripReportListRequest;
use App\Http\Requests\Reports\DriverTripReportRequest;
use App\Http\Resources\Reports\DriverTripReportPaginatedResource;
use App\Http\Resources\Reports\DriverTripReportResource;
use App\Models\Reports\DriverTripReport;
use App\Services\Events\EventService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig;
use Throwable;

class DriverTripReportController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @param  DriverTripReportListRequest  $request
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     *
     * @OA\Get(
     *     path="/api/driver-trip-report",
     *     tags={"Driver trip reports"},
     *     summary="Get driver trip report paginated list",
     *     operationId="Get driver trip reports",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="driver_id",
     *          in="query",
     *          description="Driver id",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="date_from",
     *          in="query",
     *          description="Date from",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="date_to",
     *          in="query",
     *          description="Date to",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="dates_range",
     *          in="query",
     *          description="06/06/2021 - 06/14/2021",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="report_date",
     *          in="query",
     *          description="report to",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="page",
     *          in="query",
     *          description="Page number",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *              default="5"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          description="Contacts per page",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *              default="10"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="order_by",
     *          in="query",
     *          description="Field to sort by",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="id",
     *              enum={"id","company_name"}
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="order_type",
     *          in="query",
     *          description="Sort order",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="asc",
     *              enum={"asc","desc"}
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/DriverTripReportPaginatedResource")
     *     ),
     * )
     *
     */
    public function index(DriverTripReportListRequest $request): AnonymousResourceCollection
    {
        $filterFields = $request->validated();

        $orderBy = in_array($request->input('order_by'), ['report_date', 'date_to', 'date_from'])
            ? $request->input('order_by')
            : 'created_at';

        $orderByType = in_array($request->input('order_type'), ['asc', 'desc'])
            ? $request->input('order_type')
            : 'desc';

        $perPage = (int)$request->input('per_page', 10);

        $data = DriverTripReport::selectRaw(
            "
                id,
                driver_id,
                date_to,
                date_from,
                report_date
            "
        )
            ->with('driver')
            ->filter($filterFields)
            ->orderBy($orderBy, $orderByType)
            ->paginate($perPage);
        return DriverTripReportPaginatedResource::collection($data);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *     path="/api/driver-trip-report",
     *     tags={"Driver trip reports"},
     *     summary="Create  driver trip report",
     *     operationId="Create driver trip report",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="driver_id",
     *          in="query",
     *          description="",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="report_date",
     *          in="query",
     *          description="",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="date_from",
     *          in="query",
     *          description="",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="date_to",
     *          in="query",
     *          description="",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="file",
     *          in="query",
     *          description="file",
     *          required=false,
     *          @OA\Schema(
     *              type="file",
     *          )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/DriverTripReportResource")
     *     ),
     * )
     *
     * @param DriverTripReportRequest $request
     * @return DriverTripReportResource
     * @throws AuthorizationException
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function store(DriverTripReportRequest $request): DriverTripReportResource
    {
        $this->authorize('driver-trip-reports create');

        $data = $request->validated();

        $driverTripReport = DriverTripReport::create($data);

        if (isset($data[DriverTripReport::DRIVER_FILE_FIELD_NAME])) {
            $driverTripReport->addMediaWithRandomName(
                DriverTripReport::DRIVER_FILE_COLLECTION_NAME,
                $data[DriverTripReport::DRIVER_FILE_FIELD_NAME],
                true
            );
        }

        EventService::driverTripReport($driverTripReport)
            ->user($request->user())
            ->create()
            ->broadcast();

        return DriverTripReportResource::make($driverTripReport);
    }

    /**
     * Display the specified resource.
     *
     * @param  DriverTripReport  $driverTripReport
     * @return DriverTripReportResource
     * @throws AuthorizationException
     *
     * @OA\Get(
     *     path="/api/driver-trip-report/{driverTripReportId}",
     *     tags={"Driver trip reports"},
     *     summary="Get driver trip report",
     *     operationId="Get driver trip report",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/DriverTripReportResource")
     *     ),
     * )
     *
     */
    public function show(DriverTripReport $driverTripReport): DriverTripReportResource
    {
        $this->authorize('driver-trip-reports read');

        return DriverTripReportResource::make($driverTripReport);
    }

    /**
     * Update resource in storage.
     *
     * @param DriverTripReportRequest $request
     * @param DriverTripReport $driverTripReport
     * @return DriverTripReportResource
     * @throws AuthorizationException
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @throws Throwable
     * @OA\Post(
     *     path="/api/driver-trip-report/{driverTripReportId}/update",
     *     tags={"Driver trip reports"},
     *     summary="Update driver trip report",
     *     operationId="Update driver trip report",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="driver_id",
     *          in="query",
     *          description="",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="report_date",
     *          in="query",
     *          description="",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="date_from",
     *          in="query",
     *          description="",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="date_to",
     *          in="query",
     *          description="",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="file",
     *          in="query",
     *          description="file",
     *          required=false,
     *          @OA\Schema(
     *              type="file",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/DriverTripReportResource")
     *     ),
     * )
     */
    public function update(DriverTripReportRequest $request, DriverTripReport $driverTripReport): DriverTripReportResource
    {
        $this->authorize('driver-trip-reports update');

        $data = $request->validated();

        $driverTripReport->fill($data);
        $driverTripReport->saveOrFail();
        if (isset($data[DriverTripReport::DRIVER_FILE_FIELD_NAME])) {
            $driverTripReport->addMediaWithRandomName(
                DriverTripReport::DRIVER_FILE_COLLECTION_NAME,
                $data[DriverTripReport::DRIVER_FILE_FIELD_NAME],
                true
            );
        }

        EventService::driverTripReport($driverTripReport)
            ->user($request->user())
            ->update()
            ->broadcast();

        return DriverTripReportResource::make($driverTripReport);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  DriverTripReport  $driverTripReport
     * @return JsonResponse
     * @throws AuthorizationException
     *
     * @OA\Delete(
     *     path="/api/driver-trip-report/{driverTripReportId}",
     *     tags={"Driver trip reports"},
     *     summary="Delete trip driver report",
     *     operationId="Delete trip driver report",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation",
     *     ),
     * )
     *
     */
    public function destroy(DriverTripReport $driverTripReport): JsonResponse
    {
        $this->authorize('driver-trip-reports delete');

        $driverTripReport->delete();

        EventService::driverTripReport($driverTripReport)
            ->user(request()->user())
            ->delete()
            ->broadcast();

        return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete single order attachment
     *
     * @param DriverTripReport $driverTripReport
     * @param int $id
     * @return JsonResponse|Response
     *
     * @OA\Delete(
     *     path="/api/driver-trip-report/{driverTripReportId}/file/{fileId}",
     *     tags={"Driver trip reports"},
     *     summary="Delete file from driverTripReport",
     *     operationId="Delete",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=204, description="Successful operation"),
     * )
     *
     * @throws AuthorizationException
     */
    public function deleteFile(DriverTripReport $driverTripReport, int $id)
    {
        $this->authorize('driver-trip-reports delete');

        try {
            if ($driverTripReport->media->find($id)) {
                $driverTripReport->deleteMedia($id);
            }

            EventService::driverTripReport($driverTripReport)
                ->user(request()->user())
                ->update()
                ->broadcast();

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error($e);
            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
