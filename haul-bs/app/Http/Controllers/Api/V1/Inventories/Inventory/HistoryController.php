<?php

namespace App\Http\Controllers\Api\V1\Inventories\Inventory;

use App\Foundations\Modules\History\Repositories\HistoryRepository;
use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\History\HistoryFilterRequest;
use App\Http\Resources\History\HistoryListResource;
use App\Http\Resources\History\HistoryPaginatedResource;
use App\Http\Resources\Users\UserShortListResource;
use App\Models\Inventories\Inventory;
use App\Repositories\Inventories\InventoryRepository;
use Illuminate\Http\Resources\Json\ResourceCollection;

class HistoryController extends ApiController
{
    public function __construct(
        protected InventoryRepository $repo,
        protected HistoryRepository $historyRepo,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/inventories/{id}/history",
     *     tags={"Inventory"},
     *     security={{"Basic": {}}},
     *     summary="Get inventory history",
     *     operationId="GetInventoryHistory",
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
     *     @OA\Response(response=200, description="Inventory history data",
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
        $this->authorize(Permission\Inventory\Inventory\InventoryReadPermission::KEY);

        /** @var $model Inventory */
        $model = $this->repo->getBy(
            ['id' => $id],
            ['histories'],
            withException: true,
            exceptionMessage: __("exceptions.inventories.inventory.not_found")
        );

        return HistoryListResource::collection(
            $model->histories
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/inventories/{id}/history-detailed",
     *     tags={"Inventory"},
     *     security={{"Basic": {}}},
     *     summary="Get inventory history detailed",
     *     operationId="GetInventoryHistoryDetailed",
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
     *     @OA\Response(response=200, description="Inventory history data",
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
        $this->authorize(Permission\Inventory\Inventory\InventoryReadPermission::KEY);

        /** @var $model Inventory */
        $model = $this->repo->getBy(
            ['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.inventories.inventory.not_found")
        );

        return HistoryPaginatedResource::collection(
            $this->historyRepo->getCustomPagination($model, $request->validated())
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/inventories/{id}/history-users-list",
     *     tags={"Inventory"},
     *     security={{"Basic": {}}},
     *     summary="Get list users changes inventory",
     *     operationId="GetListUsersChangesInventory",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Response(response=200, description="Inventory history data",
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
        $this->authorize(Permission\Inventory\Inventory\InventoryReadPermission::KEY);

        /** @var $model Inventory */
        $model = $this->repo->getBy(
            ['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.inventories.inventory.not_found")
        );

        return UserShortListResource::collection(
            $this->historyRepo->getHistoryUsers($model)
        );
    }
}
