<?php

namespace App\Http\Controllers\Api\V1\Orders\Parts;

use App\Foundations\Modules\Comment\Services\CommentService;
use App\Foundations\Modules\History\Services\HistoryService;
use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Common\CommentRequest;
use App\Http\Resources\Common\CommentResource;
use App\Models\Orders\Parts\Order;
use App\Repositories\Orders\Parts\OrderRepository;
use App\Services\Events\EventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CommentController extends ApiController
{
    public function __construct(
        protected OrderRepository $repo,
        protected CommentService $serviceComment,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/orders/parts/{id}/comments",
     *     tags={"Parts order"},
     *     security={{"Basic": {}}},
     *     summary="Get list comment for parts order",
     *     operationId="GetListCommentForPartsOrder",
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
        $this->authorize(Permission\Order\Parts\OrderReadPermission::KEY);

        /** @var $model Order */
        $model = $this->repo->getById($id);

        Order::assertSalesManager($model);

        return CommentResource::collection(
            $model->comments
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/orders/parts/{id}/comments",
     *     tags={"Parts order"},
     *     security={{"Basic": {}}},
     *     summary="Create comment for parts order",
     *     operationId="CreateCommentForPartsOrder",
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
        $this->authorize(Permission\Order\Parts\OrderAddCommentPermission::KEY);

        /** @var $model Order */
        $model = $this->repo->getById($id);

        Order::assertSalesManager($model);

        $comment = $this->serviceComment->create($model, auth_user(), $request->comment);

        EventService::partsOrder($model)
            ->custom(HistoryService::ACTION_COMMENT_CREATED)
            ->initiator(auth_user())
            ->setComment($comment)
            ->setHistory()
            ->exec()
        ;

        return CommentResource::make($comment);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/orders/parts/{id}/comments/{commentId}",
     *     tags={"Parts order"},
     *     security={{"Basic": {}}},
     *     summary="Delete comment from parts order",
     *     operationId="DeleteCommentFromPartsOrder",
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
        $this->authorize(Permission\Order\Parts\OrderDeleteCommentPermission::KEY);

        /** @var $model Order */
        $model = $this->repo->getById($id);

        Order::assertSalesManager($model);

        $comment = $model->comments->where('id', $commentId)->first();

        if(!$comment){
            throw new \Exception(__("exceptions.comment.not_found"), Response::HTTP_NOT_FOUND);
        }

        $clone = clone $comment;

        $this->serviceComment->delete($comment);

        EventService::partsOrder($model)
            ->custom(HistoryService::ACTION_COMMENT_DELETED)
            ->initiator(auth_user())
            ->setComment($clone)
            ->setHistory()
            ->exec()
        ;

        return $this->successJsonMessage(null, Response::HTTP_NO_CONTENT);
    }
}
