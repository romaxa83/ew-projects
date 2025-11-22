<?php

namespace App\Http\Controllers\Api\Lists;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Lists\ExpenseTypeRequest;
use App\Http\Resources\Lists\TypeListResource;
use App\Models\Lists\ExpenseType;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ExpenseTypeController extends ApiController
{
    /**
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     */
    public function list(): AnonymousResourceCollection
    {
        $this->authorize('dictionaries');

        return TypeListResource::collection(
            array_merge(
                ExpenseType::getDefaultTypesList(),
                ExpenseType::all()->toArray()
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

        $data = ExpenseType::all()->toArray();

        return TypeListResource::collection(
            array_merge(
                ExpenseType::getDefaultTypesList(),
                $data
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ExpenseTypeRequest $request
     * @return TypeListResource
     * @throws AuthorizationException
     */
    public function store(ExpenseTypeRequest $request): TypeListResource
    {
        $this->authorize('dictionaries create');

        $expenseType = new ExpenseType();
        $expenseType->fill($request->validated());
        $expenseType->save();

        return TypeListResource::make($expenseType);
    }

    /**
     * Display the specified resource.
     *
     * @param ExpenseType $expenseType
     * @return TypeListResource
     * @throws AuthorizationException
     */
    public function show(ExpenseType $expenseType): TypeListResource
    {
        $this->authorize('dictionaries read');

        return TypeListResource::make($expenseType);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ExpenseTypeRequest $request
     * @param ExpenseType $expenseType
     * @return TypeListResource
     * @throws AuthorizationException
     */
    public function update(ExpenseTypeRequest $request, ExpenseType $expenseType): TypeListResource
    {
        $this->authorize('dictionaries update');

        $expenseType->fill($request->validated());
        $expenseType->save();

        return TypeListResource::make($expenseType);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param ExpenseType $expenseType
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws Exception
     */
    public function destroy(ExpenseType $expenseType): JsonResponse
    {
        $this->authorize('dictionaries delete');

        $expenseType->delete();

        return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
    }
}

/**
 * @OA\Get(
 *     path="/api/expense-types/list",
 *     tags={"Expense types"},
 *     summary="Expense types list",
 *     operationId="Expense types list",
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
 *     path="/api/expense-types",
 *     tags={"Expense types"},
 *     summary="Get Expense types paginated list",
 *     operationId="Get Expense types data",
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
 *     path="/api/expense-types",
 *     tags={"Expense types"},
 *     summary="Create Expense type",
 *     operationId="Create Expense type",
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
 *     path="/api/expense-types/{expenseTypeId}",
 *     tags={"Expense types"},
 *     summary="Get Expense type info",
 *     operationId="Get Expense type data",
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
 *     path="/api/expense-types/{expenseTypeId}",
 *     tags={"Expense types"},
 *     summary="Update Expense type",
 *     operationId="Update Expense type",
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
 *     path="/api/expense-types/{expenseTypeId}",
 *     tags={"Expense types"},
 *     summary="Delete Expense type",
 *     operationId="Delete Expense type",
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
