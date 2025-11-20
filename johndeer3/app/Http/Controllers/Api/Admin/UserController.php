<?php

namespace App\Http\Controllers\Api\Admin;

use App\DTO\JD\UserDTO;
use App\Http\Controllers\Api\ApiController;
use App\Http\Request\User\AttachEgsUserRequest;
use App\Http\Request\User\ChangeStatusUserRequest;
use App\Http\Request\User\CreateUserRequest;
use App\Http\Request\User\UpdateUserRequest;
use App\Jobs\MailSendIosLinkJob;
use App\Jobs\MailSendJob;
use App\Models\User\User;
use App\Repositories\User\UserRepository;
use App\Resources\User\UserResource;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends ApiController
{
    private $userService;
    private $userRepository;

    public function __construct(
        UserService $userService,
        UserRepository $userRepository
    )
    {
        $this->userService = $userService;
        $this->userRepository = $userRepository;

        parent::__construct();
    }

    /**
     * @SWG\Get(
     *     path="/api/admin/user",
     *     summary="Получить всех пользователей",
     *     tags={"Аdmin-panel"},
     *     security={{"passport": {}}},
     *     @SWG\Parameter(ref="#/parameters/Auth"),
     *
     *     @SWG\Parameter(name="page", in="query", required=false, type="integer",
     *          description="Страница пагинации : 5"
     *     ),
     *     @SWG\Parameter(name="perPage", in="query", required=false, type="integer",
     *          description="Значений на страницу"
     *     ),
     *     @SWG\Parameter(name="role", in="query", description="Роль", required=false, type="string"),
     *     @SWG\Parameter(name="login", in="query", description="Login", required=false, type="string"),
     *     @SWG\Parameter(name="email", in="query", description="Email", required=false, type="string"),
     *     @SWG\Parameter(name="country_id", in="query", description="Country", required=false, type="integer"),
     *     @SWG\Parameter(name="name", in="query", required=false, type="string",
     *          description="фильтр по имени, передавать можно имя/фамилию или и имя и фамилию ,но при этом их обязателбно разделить нижним подчеркиванием (ИМЯ_ФАМИЛИЯ)"
     *     ),
     *     @SWG\Parameter(name="dealer", in="query", description="фильтр по дилеру", required=false, type="string"),
     *
     *     @SWG\Response(response=200, description="Пользователи",
     *         @SWG\Schema(ref="#/definitions/UserCollection")
     *     ),
     *     @SWG\Response(response="default", description="Ошибка валидации",
     *         @SWG\Schema(
     *            @SWG\Property(property="data", type="object", ref="#/definitions/ErrorMessage")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $users = $this->userRepository->getAllForAdmin([], $request->all());

            return UserResource::collection($users);
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/admin/user/{user}",
     *     tags = {"User (for admin)"},
     *     summary="Получить пользователя по ID",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="{user}", in="path", required=true,
     *          description="ID пользователя",
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *
     *     @OA\Response(response="200", description="Пользователь",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  ref="#/components/schemas/UserResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function show(Request $request, User $user)
    {
        try {
            return $this->successJsonMessage(
                UserResource::make($user)
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Post (
     *     path="/api/admin/user/create",
     *     tags = {"User (for admin)"},
     *     summary="Создание пользователя",
     *     security={{"Basic": {}}},
     *
     *     @OA\RequestBody(required=true,
     *          @OA\JsonContent(ref="#/components/schemas/CreateUserRequest")
     *     ),
     *
     *     @OA\Response(response="200", description="Пользователь",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  ref="#/components/schemas/UserResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function create(CreateUserRequest $request)
    {
        try {
            $dto = UserDTO::byRequest($request->all());
            $dto->password = User::generateRandomPassword();

            $model = null;
            makeTransaction(function () use($dto, &$model){
                $model = $this->userService->create($dto);
            });

            MailSendJob::dispatch([
                'type' => 'password',
                'password' => $dto->password,
                'user' => $model
            ]);

            return $this->successJsonMessage(UserResource::make($model));
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Post (
     *     path="/api/admin/user/edit/{user}",
     *     tags = {"User (for admin)"},
     *     summary="Редактирование пользователя",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="{user}", in="path", required=true,
     *          description="ID пользователя",
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *
     *     @OA\RequestBody(required=true,
     *          @OA\JsonContent(ref="#/components/schemas/UpdateUserRequest")
     *     ),
     *
     *     @OA\Response(response="200", description="Пользователь",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  ref="#/components/schemas/UserResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            $dto = UserDTO::byRequest($request->all());

            makeTransaction(fn() => $this->userService->edit($user, $dto));

            return $this->successJsonMessage(UserResource::make($user));
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Post (
     *     path="/api/admin/user/change-status/{user}",
     *     tags = {"User (for admin)"},
     *     summary="Сменить статус пользователю",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="{user}", in="path", required=true,
     *          description="ID пользователя",
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *
     *     @OA\RequestBody(required=true,
     *          @OA\JsonContent(ref="#/components/schemas/ChangeStatusUserRequest")
     *     ),
     *
     *     @OA\Response(response="200", description="Пользователь",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  ref="#/components/schemas/UserResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function changeStatus(ChangeStatusUserRequest $request, User $user)
    {
        try {
            $user = $this->userService->changeStatus($user, $request->status);

            return $this->successJsonMessage(UserResource::make($user));
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Put (
     *     path="/api/admin/user/{user}/generate-password",
     *     tags = {"User (for admin)"},
     *     summary="Сгенирировать новый пароль пользователю",
     *     description="Будет сгенерирован новый пароль, и выслан на почту пользователю, пользователь не может создавать себе пароль, это может делать только админ",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="{user}", in="path", required=true,
     *          description="ID пользователя",
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/SuccessMessageResponse")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function generatePassword(Request $request, User $user)
    {
        try {
            $user = $this->userService->savePassword($user, $password = User::generateRandomPassword());
            $user = $this->userService->addIosLink($user);

            MailSendJob::dispatch([
                'type' => 'password',
                'password' => $password,
                'user' => $user
            ]);

            return $this->successJsonMessage(__('message.generate_password_success'));
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Post (
     *     path="/api/admin/user/attach-egs/{user}",
     *     tags = {"User (for admin)"},
     *     summary="Привязывание equipment-group",
     *     description ="Привязывание к пользователю, c ролью - pss, equipment-group",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="{user}", in="path", required=true,
     *          description="ID пользователя",
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *     @OA\RequestBody(required=true,
     *          @OA\JsonContent(ref="#/components/schemas/AttachEgsUserRequest")
     *     ),
     *
     *     @OA\Response(response="200", description="Пользователь",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  ref="#/components/schemas/UserResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function attachEgs(AttachEgsUserRequest $request, User $user)
    {
        try {
            $user = $this->userService->attachEgs($request['eg_ids'], $user);

            return $this->successJsonMessage(UserResource::make($user));
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Put (
     *     path="/api/admin/user/{user}/send-ios-link",
     *     tags = {"User (for admin)"},
     *     summary="Выслать ссылку на приложение ios",
     *     description ="Выслать ссылку для скачивания приложения (для ios) на почту пользователя",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="{user}", in="path", required=true,
     *          description="ID пользователя",
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *
     *     @OA\Response(response="200", description="Пользователь",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  ref="#/components/schemas/UserResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function sendIosLink(User $user)
    {
        try {
            $user = $this->userService->addIosLink($user);

            MailSendIosLinkJob::dispatch(['user' => $user]);

            return $this->successJsonMessage(UserResource::make($user));
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage(), $error->getCode());
        }
    }
}
