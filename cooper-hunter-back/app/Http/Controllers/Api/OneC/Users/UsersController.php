<?php

namespace App\Http\Controllers\Api\OneC\Users;

use App\Dto\UpdateGuidDto;
use App\Dto\Users\UserDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OneC\Users\UserCreateRequest;
use App\Http\Requests\Api\OneC\Users\UsersImportRequest;
use App\Http\Requests\Api\OneC\Users\UsersIndexRequest;
use App\Http\Requests\Api\OneC\Users\UserUpdateGuidRequest;
use App\Http\Requests\Api\OneC\Users\UserUpdateRequest;
use App\Http\Resources\Api\OneC\Users\UserResource;
use App\Models\Users\User;
use App\Permissions\Users\UserDeletePermission;
use App\Permissions\Users\UserListPermission;
use App\Services\UpdateGuidService;
use App\Services\Users\UserService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Throwable;

/**
 * @group Users
 */
class UsersController extends Controller
{
    /**
     * List
     *
     * @permission user.user.list
     *
     * @responseFile docs/api/users/list.json
     */
    public function index(UsersIndexRequest $request): AnonymousResourceCollection
    {
        return UserResource::collection(
            User::query()
                ->select(['id', 'email', 'phone', 'guid'])
                ->paginate($request->get('per_page'))
        );
    }

    /**
     * New
     *
     * @permission user.user.list
     *
     * @responseFile docs/api/users/list.json
     */
    public function new(UsersIndexRequest $request): AnonymousResourceCollection
    {
        return UserResource::collection(
            User::query()
                ->select(['id', 'email', 'phone', 'guid'])
                ->new()
                ->paginate($request->get('per_page'))
        );
    }

    /**
     * Show
     *
     * @permission user.user.list
     *
     * @responseFile docs/api/users/single.json
     * @throws AuthorizationException
     */
    public function show(User $user): UserResource
    {
        $this->authorize(UserListPermission::KEY);

        return new UserResource($user);
    }

    /**
     * Store
     *
     * @permission user.user.create
     *
     * @responseFile 201 docs/api/users/single.json
     *
     * @throws Throwable
     */
    public function store(UserCreateRequest $request, UserService $service): UserResource
    {
        return makeTransaction(
            static fn() => new UserResource(
                $service->register(
                    UserDto::byArgs($request->validated())
                )
            )
        );
    }

    /**
     * Update
     *
     * @permission user.user.update
     *
     * @responseFile docs/api/users/single.json
     *
     * @throws Throwable
     */
    public function update(User $user, UserUpdateRequest $request, UserService $service): UserResource
    {
        return makeTransaction(
            static fn() => new UserResource(
                $service->update(
                    $user,
                    UserDto::byArgs($request->validated()),
                )
            )
        );
    }

    /**
     * Update guid
     *
     * @permission user.user.update
     *
     * @responseFile docs/api/users/update-guid.json
     *
     * @throws Throwable
     */
    public function updateGuid(UserUpdateGuidRequest $request, UpdateGuidService $service): AnonymousResourceCollection
    {
        $response = [];

        $ids = collect($request->get('data'))->pluck('id');
        $entities = User::query()->whereKey($ids)->get();

        foreach ($request->get('data') as $userData) {
            $response[] = makeTransaction(
                static function () use ($service, $userData, $entities) {
                    $dto = UpdateGuidDto::byArgs($userData);

                    return $service->updateGuid(
                        $entities->where('id', $dto->getId())->first(),
                        $dto
                    );
                }
            );
        }

        return UserResource::collection($response);
    }

    /**
     * Destroy
     *
     * @permission user.user.delete
     *
     * @response {
     * "success": true,
     * "message": "User deleted"
     * }
     *
     * @throws Throwable
     * @throws AuthorizationException
     */
    public function destroy(User $user, UserService $service): JsonResponse
    {
        $this->authorize(UserDeletePermission::KEY);

        $service->delete((new Collection())->add($user));

        return $this->success('User deleted');
    }

    /**
     * Import
     *
     * @permission user.user.create
     *
     * @responseFile 201 docs/api/users/list.json
     *
     * @throws Throwable
     */
    public function import(UsersImportRequest $request, UserService $service): AnonymousResourceCollection
    {
        $response = [];

        foreach ($request->get('users') as $userData) {
            $response[] = makeTransaction(
                static function () use ($service, $userData) {
                    return $service->register(
                        UserDto::byArgs($userData)
                    );
                }
            );
        }

        return UserResource::collection($response);
    }
}
