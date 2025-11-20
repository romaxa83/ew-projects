<?php

namespace App\Http\Controllers\Api\V1\Employees;

use App\Enums\Employees\Status;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Employees\EmployeeUpdateRequest;
use App\Http\Resources\Employees\EmployeeSimpleResource;
use App\Models\Employees\Employee;
use App\Repositories\Employees\EmployeeRepository;
use App\Services\Employees\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EmployeeController extends ApiController
{
    public function __construct(
        protected EmployeeRepository $repo,
        protected EmployeeService $service,
    )
    {}

    /**
     * @OA\Get(
     *     path="/employees",
     *     tags={"Employees"},
     *     security={
     *       {"Basic": {}},
     *     },
     *     summary="A list of employees",
     *
     *     @OA\Response(response="200", description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="array",
     *                  @OA\Items(ref="#/components/schemas/EmployeeSimpleResource")
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function list(): JsonResponse|ResourceCollection
    {
        try {
            return $this->successJsonMessage(
                EmployeeSimpleResource::collection($this->repo->getAll([
                    'sip',
                    'department',
                ]))
            );
        } catch (\Throwable $e){
            return $this->errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @OA\Post(
     *     path="/employees/{id}",
     *     tags={"Employees"},
     *     security={
     *       {"Basic": {}},
     *     },
     *
     *     @OA\Parameter(name="id", in="path", required=true,
     *          description="ID employee",
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *
     *     summary="Update employee",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/EmployeeUpdateRequest")),
     *
     *     @OA\Response(response="200", description="Отчет",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="The model being edited is returned", type="object",
     *                  ref="#/components/schemas/EmployeeSimpleResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function edit(EmployeeUpdateRequest $request, $id): JsonResponse|ResourceCollection
    {
        try {
            /** @var $model Employee */
            $model = $this->service->repo->getBy(
                field: 'id',
                value:  $id,
                withException: true,
                exceptionMessage: "Employee not found by id - [{$id}]"
            );

            return $this->successJsonMessage(
                EmployeeSimpleResource::make(
                    $this->service->changeStatus(
                        $model,
                        Status::fromValue($request['status'])
                    )
                )
            );
        } catch (\Throwable $e){
            return $this->errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }
}
