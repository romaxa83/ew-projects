<?php

namespace App\Http\Controllers\Api\V1\Customers;

use App\Foundations\Modules\Comment\Services\CommentService;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Common\CommentRequest;
use App\Http\Resources\Common\CommentResource;
use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Models\Customers\Customer;
use App\Repositories\Customers\CustomerRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CustomerCommentController extends ApiController
{
    public function __construct(
        protected CustomerRepository $repo,
        protected CommentService $serviceComment,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/customers/{id}/comments",
     *     tags={"Customers"},
     *     security={{"Basic": {}}},
     *     summary="Get list comment for customer",
     *     operationId="GetListCommentForCustomer",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Response(response=200, description="Comment data",
     *         @OA\JsonContent(ref="#/components/schemas/CommentListResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function index($id): AnonymousResourceCollection
    {
        $this->authorize(Permission\VehicleOwner\VehicleOwnerReadPermission::KEY);

        /** @var $model Customer */
        $model = $this->repo->getBy(['id' => $id], ['comments.author.roles'],
            withException: true,
            exceptionMessage: __("exceptions.customer.not_found")
        );

        return CommentResource::collection(
            $model->comments
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/customers/{id}/comments",
     *     tags={"Customers"},
     *     security={{"Basic": {}}},
     *     summary="Create comment for customer",
     *     operationId="CreateCommentForCustomer",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CommentRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Comment data",
     *         @OA\JsonContent(ref="#/components/schemas/CommentResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function store(CommentRequest $request, $id): CommentResource
    {
        $this->authorize(Permission\VehicleOwner\VehicleOwnerAddCommentPermission::KEY);

        /** @var $model Customer */
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.customer.not_found")
        );

        return CommentResource::make(
            $this->serviceComment->create($model, auth_user(), $request->comment)
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/customers/{id}/comments/{commentId}",
     *     tags={"Customers"},
     *     security={{"Basic": {}}},
     *     summary="Delete comment from customer",
     *     operationId="DeleteCommentFromCustomer",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Parameter(name="{commentId}", in="path", required=true, description="ID comment entity",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(response=204, description="Successful delete"),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function delete($id, $commentId): JsonResponse
    {
        $this->authorize(Permission\VehicleOwner\VehicleOwnerDeleteCommentPermission::KEY);

        /** @var $model Customer */
        $model = $this->repo->getBy(['id' => $id], ['comments'],
            withException: true,
            exceptionMessage: __("exceptions.customer.not_found")
        );

        $comment = $model->comments->where('id', $commentId)->first();

        if(!$comment){
            throw new \Exception(__("exceptions.comment.not_found"), Response::HTTP_NOT_FOUND);
        }

        $this->serviceComment->delete($comment);

        return $this->successJsonMessage(null, Response::HTTP_NO_CONTENT);
    }
}
