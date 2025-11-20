<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Repositories\User\RoleRepository;

class RoleController extends ApiController
{
    public function __construct(
        protected RoleRepository $repo
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/api/admin/role",
     *     tags = {"User (for admin)"},
     *     summary="Получить ролей",
     *     description ="Получение списка все ролей (кроме admin), для выбора при создании пользователя",
     *     security={{"Basic": {}}},
     *
     *     @OA\Response(response="200", description="Success with simple data", @OA\JsonContent(ref="#/components/schemas/SuccessWithSimpleData")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function list()
    {
        try {
            return $this->successJsonMessage(
                $this->repo->getRoles()
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }
}
