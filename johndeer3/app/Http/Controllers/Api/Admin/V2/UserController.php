<?php

namespace App\Http\Controllers\Api\Admin\V2;

use App\Http\Controllers\Api\ApiController;
use App\Repositories\User\UserRepository;
use App\Resources\User\UserResource;
use Illuminate\Http\Request;

class UserController extends ApiController
{
    protected $orderBySupport = ['id', 'created_at'];
    protected $defaultOrderBy = 'created_at';

    public function __construct(
        protected UserRepository $repo
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/api/v2/admin/user",
     *     tags = {"User (for admin)"},
     *     summary="Получить всех пользователей",
     *     description ="Получение пользователей с пагинацией используя различные фильтрации и сортировку",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="page", in="query", required=false,
     *          description="Страница пагинации",
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *     @OA\Parameter(name="per_page", in="query", required=false,
     *          description="Количество записей на странице",
     *          @OA\Schema(type="integer", example="15", default=10)
     *     ),
     *     @OA\Parameter(name="role", in="query", required=false,
     *          description="Фильтр по роли",
     *          @OA\Schema(type="string", example="pss")
     *     ),
     *     @OA\Parameter(name="login", in="query", required=false,
     *          description="Фильтр по логину",
     *          @OA\Schema(type="string", example="cubic")
     *     ),
     *     @OA\Parameter(name="email", in="query", required=false,
     *          description="Фильтр по email",
     *          @OA\Schema(type="string", example="cubic@rubic.com")
     *     ),
     *     @OA\Parameter(name="country_id", in="query", required=false,
     *          description="Фильтр по национальности, список получаем здесь - api/admin/nationalities",
     *          @OA\Schema(type="integer", example=2)
     *     ),
     *     @OA\Parameter(name="name", in="query", required=false,
     *          description="Фильтр по имени, передавать можно имя/фамилию или и имя и фамилию ,но при этом их обязателбно разделить нижним подчеркиванием (ИМЯ_ФАМИЛИЯ)",
     *          @OA\Schema(type="string", example="Cubic_Rubic")
     *     ),
     *     @OA\Parameter(name="dealer", in="query", required=false,
     *          description="Фильтр по имени дилера",
     *          @OA\Schema(type="string", example="Agristar")
     *     ),
     *     @OA\Parameter(name="order_by", in="query", required=false,
     *          description="Поле, по которому происходит сортировка",
     *          @OA\Schema(type="string", example="id", default="created_at", enum={"id", "created_at"})
     *     ),
     *     @OA\Parameter(name="order_type", in="query", required=false,
     *          description="Тип сортировки",
     *          @OA\Schema(type="string", example="asc", default="desc", enum={"asc", "desc"})
     *     ),
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/UserCollections")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function index(Request $request)
    {
        try {
            $users = $this->repo->getAllForAdmin(
                ['roles', 'profile', 'dealer', 'dealers'],
                $request->all(),
                $this->orderDataForQuery(),
                false
            );

            return UserResource::collection($users);
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }
}

