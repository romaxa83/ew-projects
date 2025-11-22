<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Helpers\Logger\AALogger;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\User\UserEditRequest;
use App\Models\User\User;
use App\Repositories\User\UserRepository;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends ApiController
{
    public function __construct(
        protected UserRepository $repository,
        protected UserService $service,
    )
    {}

    /**
     * @OA\Post (
     *     path="users/{id}/edit",
     *     tags={"User"},
     *     security={
     *       {"Basic": {}},
     *     },
     *     summary="Edit user",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UserEdit")),
     *
     *     @OA\Response(response="200", description="OK", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function edit(UserEditRequest $request, $id): JsonResponse
    {
        AALogger::info("Запрос на редактирование пользователя [{$id}]", $request->all());
        try {
            /** @var $user User */
            $user = $this->repository->findOneBy('uuid', $id);

            $this->service->editFromAA($user, $request->all());

            return $this->successJsonMessage([]);
        } catch (\Throwable $e){
            AALogger::error($e->getMessage());
            return $this->errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }
}

