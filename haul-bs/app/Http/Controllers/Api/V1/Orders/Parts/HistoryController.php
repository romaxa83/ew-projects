<?php

namespace App\Http\Controllers\Api\V1\Orders\Parts;

use App\Foundations\Modules\History\Repositories\HistoryRepository;
use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\History\HistoryFilterRequest;
use App\Http\Resources\History\HistoryListResource;
use App\Http\Resources\History\HistoryPaginatedResource;
use App\Http\Resources\Users\UserShortListResource;
use App\Models\Orders\Parts\Order;
use App\Repositories\Orders\Parts\OrderRepository;
use Illuminate\Http\Resources\Json\ResourceCollection;

class HistoryController extends ApiController
{
    public function __construct(
        protected OrderRepository $repo,
        protected HistoryRepository $historyRepo,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/orders/parts/{id}/history",
     *     tags={"Parts order"},
     *     security={{"Basic": {}}},
     *     summary="Get parts order history",
     *     operationId="GetPartsOrderHistory",
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
     *     @OA\Response(response=200, description="Order history data",
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
        $this->authorize(Permission\Order\Parts\OrderReadPermission::KEY);

        /** @var $model Order */
        $model = $this->repo->getById($id);

        Order::assertSalesManager($model);

        return HistoryListResource::collection(
            $model->histories
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/orders/parts/{id}/history-detailed",
     *     tags={"Parts order"},
     *     security={{"Basic": {}}},
     *     summary="Get parts order history detailed",
     *     operationId="GetPartsOrderHistoryDetailed",
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
     *     @OA\Response(response=200, description="Order history data",
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
        $this->authorize(Permission\Order\Parts\OrderReadPermission::KEY);

        /** @var $model Order */
        $model = $this->repo->getById($id);

        Order::assertSalesManager($model);

        return HistoryPaginatedResource::collection(
            $this->historyRepo->getCustomPagination($model, $request->validated())
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/orders/parts/{id}/history-users-list",
     *     tags={"Parts order"},
     *     security={{"Basic": {}}},
     *     summary="Get list users changes parts order",
     *     operationId="GetListUsersChangesPartsOrder",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Response(response=200, description="Order history data",
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
        $this->authorize(Permission\Order\Parts\OrderReadPermission::KEY);

        /** @var $model Order */
        $model = $this->repo->getById($id);

        Order::assertSalesManager($model);

        return UserShortListResource::collection(
            $this->historyRepo->getHistoryUsers($model)
        );
    }
}
