<?php

namespace App\Http\Controllers\Api\V1\Vehicles\Trailer;

use App\Foundations\Modules\History\Repositories\HistoryRepository;
use App\Http\Controllers\Api\ApiController;
use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Http\Requests\History\HistoryFilterRequest;
use App\Http\Resources\History\HistoryListResource;
use App\Http\Resources\History\HistoryPaginatedResource;
use App\Http\Resources\Users\UserShortListResource;
use App\Models\Vehicles\Trailer;
use App\Repositories\Vehicles\TrailerRepository;
use Illuminate\Http\Resources\Json\ResourceCollection;

class HistoryController extends ApiController
{
    public function __construct(
        protected TrailerRepository $repo,
        protected HistoryRepository $historyRepo,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/trailers/{id}/history",
     *     tags={"Vehicles trailer"},
     *     security={{"Basic": {}}},
     *     summary="Get trailer history",
     *     operationId="GetTrailerHistory",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Response(response=200, description="Trailer history data",
     *          @OA\JsonContent(ref="#/components/schemas/HistoryListResource")
     *      ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function history($id): ResourceCollection
    {
        $this->authorize(Permission\Trailer\TrailerReadPermission::KEY);

        /** @var $model Trailer */
        $model = $this->repo->getBy(
            ['id' => $id],
            ['histories'],
            withException: true,
            exceptionMessage: __("exceptions.vehicles.trailer.not_found")
        );

        return HistoryListResource::collection(
            $model->histories
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/trailers/{id}/history-detailed",
     *     tags={"Vehicles trailer"},
     *     security={{"Basic": {}}},
     *     summary="Get trailer history detailed",
     *     operationId="GetTrailerHistoryDetailed",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/Page"),
     *     @OA\Parameter(ref="#/components/parameters/PerPage"),
     *
     *     @OA\Parameter(name="dates_range", in="query", description="06/06/2021 - 06/14/2021", required=false,
     *         @OA\Schema(type="string", example="06/06/2021 - 06/14/2021")
     *     ),
     *     @OA\Parameter(name="user_id", in="query", description="user_id", required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(response=200, description="Trailer history data",
     *          @OA\JsonContent(ref="#/components/schemas/HistoryPaginatedResource")
     *      ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function historyDetailed(HistoryFilterRequest $request,  $id): ResourceCollection
    {
        $this->authorize(Permission\Trailer\TrailerReadPermission::KEY);

        /** @var $model Trailer */
        $model = $this->repo->getBy(
            ['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.vehicles.trailer.not_found")
        );

        return HistoryPaginatedResource::collection(
            $this->historyRepo->getCustomPagination($model, $request->validated())
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/trailers/{id}/history-users-list",
     *     tags={"Vehicles trailer"},
     *     security={{"Basic": {}}},
     *     summary="Get list users changes trailer",
     *     operationId="GetListUsersChangesTrailer",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Response(response=200, description="Trailer history data",
     *          @OA\JsonContent(ref="#/components/schemas/UserShortListResource")
     *      ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function historyUsers($id): ResourceCollection
    {
        $this->authorize(Permission\Trailer\TrailerReadPermission::KEY);

        /** @var $model Trailer */
        $model = $this->repo->getBy(
            ['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.vehicles.trailer.not_found")
        );

        return UserShortListResource::collection(
            $this->historyRepo->getHistoryUsers($model)
        );
    }
}
