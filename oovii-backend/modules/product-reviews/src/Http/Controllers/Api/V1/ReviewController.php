<?php

namespace WezomCms\ProductReviews\Http\Controllers\Api\V1;

use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use InvalidArgumentException;
use Log;
use Throwable;
use WezomCms\Catalog\Repositories\ProductRepository;
use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\ProductReviews\Dto\ReviewDto;
use WezomCms\ProductReviews\Http\Requests\Api\V1\ReviewRequest;
use WezomCms\ProductReviews\Http\Resources\V1\ReviewResource;
use WezomCms\ProductReviews\Services\ReviewService;
use WezomCms\Users\Models\User;

class ReviewController extends ApiController
{
    public function __construct(
        protected ProductRepository $productRepo,
        protected ReviewService $service,
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Post (
     *     path="/mobile/products/{id}/review",
     *     tags={"Product"},
     *     security={{"Basic": {}}},
     *     summary="Create review by product",
     *
     *     @OA\RequestBody(required=true,
     *           @OA\JsonContent(ref="#/components/schemas/ReviewRequest")
     *     ),
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", type="object",
     *                  ref="#/components/schemas/ReviewResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *              @OA\Property(property="code", title="Code", example=0),
     *          )
     *     ),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="404", description="Not found", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @param ReviewRequest $request
     * @param $productID
     * @return JsonResponse
     */
    public function create(ReviewRequest $request, $productID): JsonResponse
    {
        /** @var $user User */
        $user = Auth::user();

        try {
            $product = $this->productRepo->existBy('id', $productID);
            if(!$product){
                throw new InvalidArgumentException(
                    __('cms-catalog::admin.products.exception.not found by id', [
                        'id' => $productID
                    ]), Response::HTTP_NOT_FOUND
                );
            }
            $data = $request->all();

            $data['product_id'] = $productID;

            $dto = ReviewDto::byRequest($data, $user);
            $review = $this->service->create($dto);

            return self::successJsonMessage(ReviewResource::make($review), Response::HTTP_CREATED);
        } catch (Throwable $e) {
            Log::error($e->getMessage());

            return self::errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }
}
