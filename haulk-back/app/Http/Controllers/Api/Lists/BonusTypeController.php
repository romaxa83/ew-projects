<?php

namespace App\Http\Controllers\Api\Lists;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Lists\BonusTypeRequest;
use App\Http\Resources\Lists\TypeListResource;
use App\Models\Lists\BonusType;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class BonusTypeController extends ApiController
{
    /**
     * @return AnonymousResourceCollection
     */
    public function list(): AnonymousResourceCollection
    {
        return TypeListResource::collection(
            array_merge(
                BonusType::getDefaultTypesList(),
                BonusType::all()->toArray()
            )
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('dictionaries');

        $data = BonusType::all()->toArray();

        return TypeListResource::collection(
            array_merge(
                BonusType::getDefaultTypesList(),
                $data
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param BonusTypeRequest $request
     * @return TypeListResource
     * @throws AuthorizationException
     */
    public function store(BonusTypeRequest $request): TypeListResource
    {
        $this->authorize('dictionaries create');

        $bonusType = new BonusType();
        $bonusType->fill($request->validated());
        $bonusType->save();

        return TypeListResource::make($bonusType);
    }

    /**
     * Display the specified resource.
     *
     * @param BonusType $bonusType
     * @return TypeListResource
     * @throws AuthorizationException
     */
    public function show(BonusType $bonusType): TypeListResource
    {
        $this->authorize('dictionaries read');

        return TypeListResource::make($bonusType);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param BonusTypeRequest $request
     * @param BonusType $bonusType
     * @return TypeListResource
     * @throws AuthorizationException
     */
    public function update(BonusTypeRequest $request, BonusType $bonusType): TypeListResource
    {
        $this->authorize('dictionaries update');

        $bonusType->fill($request->validated());
        $bonusType->save();

        return TypeListResource::make($bonusType);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param BonusType $bonusType
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Exception
     */
    public function destroy(BonusType $bonusType): JsonResponse
    {
        $this->authorize('dictionaries delete');

        $bonusType->delete();

        return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
    }
}

/**
 *
 * @OA\Get(
 *     path="/api/bonus-types/list",
 *     tags={"Bonus types"},
 *     summary="Bonus types list",
 *     operationId="Bonus types list",
 *     deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *     ),
 * )
 *
 * @OA\Get(
 *     path="/api/bonus-types",
 *     tags={"Bonus types"},
 *     summary="Get Bonus types paginated list",
 *     operationId="Get Bonus types data",
 *     deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *     @OA\Parameter(name="page", in="query", description="Page number", required=false,
 *          @OA\Schema(type="integer", default="5")
 *     ),
 *     @OA\Parameter(name="per_page", in="query", description="Records per page", required=false,
 *          @OA\Schema(type="integer", default="10")
 *     ),
 *     @OA\Parameter(name="order_by", in="query", description="Field to sort by", required=false,
 *          @OA\Schema(type="string", default="id", enum ={"id"})
 *     ),
 *     @OA\Parameter(name="order_type", in="query", description="Sort order", required=false,
 *          @OA\Schema(type="string", default="asc",enum ={"asc","desc"})
 *     ),
 *     @OA\Response(response=200, description="Successful operation",
 *     ),
 * )
 *
 * @OA\Post(
 *     path="/api/bonus-types",
 *     tags={"Bonus types"},
 *     summary="Create Bonus type",
 *     operationId="Create Bonus type",
 *     deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *     @OA\Parameter(
 *          name="title",
 *          in="query",
 *          required=true,
 *          @OA\Schema(
 *              type="string",
 *          )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Successful operation",
 *     ),
 * )
 *
 * @OA\Get(
 *     path="/api/bonus-types/{bonusTypeId}",
 *     tags={"Bonus types"},
 *     summary="Get Bonus type info",
 *     operationId="Get Bonus type data",
 *     deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *     @OA\Response(response=200, description="Successful operation",
 *     ),
 * )
 *
 * @OA\Put(
 *     path="/api/bonus-types/{bonusTypeId}",
 *     tags={"Bonus types"},
 *     summary="Update Bonus type",
 *     operationId="Update Bonus type",
 *     deprecated=false,
 *     @OA\Parameter(ref="#/components/parameters/Content-type"),
 *     @OA\Parameter(ref="#/components/parameters/Accept"),
 *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
 *     @OA\Parameter(ref="#/components/parameters/Authorization"),
 *     @OA\Parameter(
 *          name="title",
 *          in="query",
 *          required=true,
 *          @OA\Schema(
 *              type="string",
 *          )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *     ),
 * )
 *
 * @OA\Delete(
 *     path="/api/bonus-types/{bonusTypeId}",
 *     tags={"Bonus types"},
 *     summary="Delete Bonus type",
 *     operationId="Delete Bonus type",
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
