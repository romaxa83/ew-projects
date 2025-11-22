<?php

namespace App\Http\Controllers\Api\History;

use App\Http\Resources\History\HistoryListResource;
use App\Models\History\History;
use App\Http\Controllers\ApiController;
use App\Http\Resources\History\HistoryPaginatedResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HistoryController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     *
     * @OA\Get(
     *     path="/api/history",
     *     tags={"History"},
     *     summary="Get history paginated list",
     *     operationId="Get history list",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
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
     *          description="Records per page",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *              default="10"
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
     *              enum ={"asc","desc"}
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/HistoryPaginatedResource")
     *     ),
     * )
     *
     */
    public function index(Request $request)
    {
        $this->authorize('history');

        $orderByType = in_array($request->input('order_type'), ['asc', 'desc']) ? $request->input('order_type') : 'desc';
        $perPage = (int) $request->input('per_page', 10);

        $history = History::filter($request->only(['category', 'date_from', 'date_to']))
            ->orderBy('id', $orderByType)
            ->paginate($perPage);

        if ($history) {
            foreach ($history as &$h) {
                $h['message'] = trans($h['message'], isset($h['meta']) && is_array($h['meta']) ? $h['meta'] : []);
            }
        }

        return HistoryPaginatedResource::collection($history);
    }

    /**
     * Display the specified resource.
     *
     * @param  History  $history
     * @return HistoryListResource
     * @throws AuthorizationException
     *
     * @OA\Get(
     *     path="/api/history/{historyId}",
     *     tags={"History"},
     *     summary="Get history",
     *     operationId="Get history",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/HistoryListResource")
     *     ),
     * )
     *
     */
    public function show(History $history)
    {
        $this->authorize('history');

        return HistoryListResource::make($history);
    }
}
