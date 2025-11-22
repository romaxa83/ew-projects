<?php

namespace App\Http\Controllers\Api\V1\Forms;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Forms\DraftResource;
use App\Models\Forms\Draft;
use App\Models\Users\User;
use App\Repositories\Forms\DraftRepository;
use App\Services\Forms\DraftService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class DraftCrudController extends ApiController
{
    public function __construct(
        protected DraftRepository $repo,
        protected DraftService $service,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/forms/drafts/{path}",
     *     tags={"Drafts"},
     *     security={{"Basic": {}}},
     *     summary="Get draft attributes for some form",
     *     operationId="GetDraftAttributesForSomeForm",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(name="path", in="path", description="Draft path", required=true,
     *         @OA\Schema(type="string", example="super_unique_slug")
     *     ),
     *
     *     @OA\Response(response=200, description="Form data",
     *         @OA\JsonContent(ref="#/components/schemas/DraftResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function show(string $path): DraftResource
    {
        /** @var $user User */
        $user = auth_user();

        return DraftResource::make(
            $this->repo->getBy([
                'user_id' => $user->id,
                'path' => $path
            ],
                withException: true
            )
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/forms/drafts/{path}",
     *     tags={"Drafts"},
     *     security={{"Basic": {}}},
     *     summary="Store draft data from form",
     *     operationId="StoreDraftDataFromForm",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(name="path", in="path", description="Draft path", required=true,
     *         @OA\Schema(type="string", example="super_unique_slug")
     *     ),
     *     @OA\Parameter(name="field1", in="query", description="Some filed attribute for draft", required=true,
     *           @OA\Schema(type="string", example="Text for field1")
     *      ),
     *
     *     @OA\Response(response=200, description="Successful operation",),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function store(Request $request, string $path): JsonResponse
    {
        /** @var $user User */
        $user = auth_user();

        $this->service->createOrUpdate($user, $path, $request->except('path'));

        return $this->successJsonMessage();
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/forms/drafts/{path}",
     *     tags={"Drafts"},
     *     security={{"Basic": {}}},
     *     summary="Delete draft for form",
     *     operationId="DeleteDraftForForm",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(name="path", in="path", description="Draft path", required=true,
     *         @OA\Schema(type="string", example="super_unique_slug")
     *     ),
     *
     *     @OA\Response(response=204, description="Successful delete"),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function delete(string $path): JsonResponse
    {
        /** @var $user User */
        $user = auth_user();
        /** @var $model Draft */
        $model = $this->repo->getBy([
            'user_id' => $user->id,
            'path' => $path
        ], withException: true);

        $this->service->delete($model);

        return $this->successJsonMessage(null, Response::HTTP_NO_CONTENT);
    }
}
