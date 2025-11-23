<?php

namespace WezomCms\Orders\Http\Controllers\Api\V1;

use Auth;
use DomainException;
use Exception;
use Illuminate\Http\JsonResponse;
use Log;
use Symfony\Component\HttpFoundation\Response;
use WezomCms\Catalog\Models\Product;
use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\Orders\Cart\CartItem;
use WezomCms\Orders\Contracts\CartInterface;
use WezomCms\Orders\Http\Requests\Api\V1\SetQuantityRequest;
use WezomCms\Orders\Http\Resources\V1\CartResource;
use WezomCms\Orders\Http\Requests\Api\V1\AddProductToCartRequest;
use WezomCms\Orders\Http\Resources\V1\SeparatedCartResource;
use WezomCms\Users\Models\User;
use WezomCms\Users\Services\UserService;

class CartController extends ApiController
{
    /**
     * @OA\Get (
     *     path="/mobile/cart",
     *     tags={"Cart"},
     *     @OA\Parameter(in="header", name="Cart-hash", explode=true,
     *          @OA\Schema(type="string"),
     *      ),
     *     summary="Get user cart by hash",
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", type="object",
     *                  ref="#/components/schemas/CartResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *              @OA\Property(property="code", title="Code", example=0),
     *          )
     *     ),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function getUserCart(): JsonResponse
    {
        try {
            $cart = resolve(CartInterface::class);

            return self::successJsonMessage(CartResource::make($cart));
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return self::errorJsonMessage($e->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/mobile/cart/separated",
     *     tags={"Cart"},
     *     security={{"Basic": {}}},
     *     @OA\Parameter(in="header", name="Cart-hash", explode=true,
     *          @OA\Schema(type="string"),
     *      ),
     *     summary="Get user cart separated to orders",
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", type="object",
     *                  ref="#/components/schemas/SeparatedCartResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *              @OA\Property(property="code", title="Code", example=0),
     *          )
     *     ),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function getSeparatedUserCart(): JsonResponse
    {
        try {
            $cart = resolve(CartInterface::class);

            return self::successJsonMessage(SeparatedCartResource::make($cart));
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return self::errorJsonMessage($e->getMessage());
        }
    }

    /**
     * @OA\Post (
     *     path="/mobile/cart/add",
     *     tags={"Cart"},
     *     @OA\Parameter(in="header", name="Cart-hash", explode=true,
     *          @OA\Schema(type="string"),
     *      ),
     *     summary="Add product to cart",
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/AddProductToCartRequest")),
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", type="object",
     *                  ref="#/components/schemas/CartResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *              @OA\Property(property="code", title="Code", example=0),
     *          )
     *     ),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @param AddProductToCartRequest $request
     * @param CartInterface $cart
     * @return JsonResponse
     */
    public function addProductToCart(AddProductToCartRequest $request, CartInterface $cart): JsonResponse
    {
        try {
            /** @var Product $product */
            $product = Product::find($request->input('product_id'));

            if (!$product) {
                throw new DomainException(__('cms-order::site.errors.Product not found'), Response::HTTP_NOT_FOUND);
            }

            if (!$product->availableForPurchase()) {
                throw new DomainException(__('cms-order::site.errors.Product not available for purchase'), Response::HTTP_NOT_FOUND);
            }

            $quantity = $request->input('quantity') ?? $product->minCountForPurchase();

            $cartQuantity = $cart->productQuantity($product->id);

            if (!$product->validatePurchaseQuantity($cartQuantity + $quantity)) {
                throw new DomainException(__('cms-order::site.errors.Forbidden quantity'), Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $cart->add($product, $quantity);

            return self::successJsonMessage(CartResource::make($cart));
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return self::errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @OA\Post (
     *     path="/mobile/cart/set-quantity",
     *     tags={"Cart"},
     *     @OA\Parameter(in="header", name="Cart-hash", explode=true,
     *          @OA\Schema(type="string"),
     *      ),
     *     summary="Set cart item quantity",
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/SetQuantityRequest")),
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", type="object",
     *                  ref="#/components/schemas/CartResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *              @OA\Property(property="code", title="Code", example=0),
     *          )
     *     ),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @param SetQuantityRequest $request
     * @param CartInterface $cart
     * @return JsonResponse
     */
    public function setQuantity(SetQuantityRequest $request, CartInterface $cart): JsonResponse
    {
        try {
            /** @var CartItem $cartItem */
            if ($cartItem = $cart->get($request->input('unique_id'))) {
                $purchaseItem = $cartItem->getPurchaseItem();
                if ($purchaseItem->validatePurchaseQuantity($request->input('quantity'))) {
                    $cartItem->setQuantity($request->input('quantity'));
                } else {
                    throw new DomainException(__('cms-order::site.errors.Forbidden quantity'), Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }

            return self::successJsonMessage(CartResource::make($cart));
        } catch (Exception $e) {
            return self::errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @OA\Delete (
     *     path="/mobile/cart/remove/{uniqueId}",
     *     tags={"Cart"},
     *     summary="Delete item from cart",
     *
     *     @OA\Parameter(name="uniqueId", in="path", required=true,
     *         description="Идентификатор позиции в корзине",
     *         @OA\Schema(type="string",example="09f29efdfb0bdc363c452af3432d8ff1")
     *     ),
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", type="object",
     *                  ref="#/components/schemas/CartResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *              @OA\Property(property="code", title="Code", example=0),
     *          )
     *     ),
     *
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @param string $uniqueId
     * @param CartInterface $cart
     * @return JsonResponse
     */
    public function remove(string $uniqueId, CartInterface $cart): JsonResponse
    {
        try {
            /** @var CartItem $cartItem */
            if ($cartItem = $cart->get($uniqueId)) {
                $cart->remove($uniqueId);
            }

            return self::successJsonMessage(CartResource::make($cart));
        } catch (Exception $e) {
            return self::errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @OA\Get (
     *     path="/mobile/cart/clear",
     *     tags={"Cart"},
     *     @OA\Parameter(in="header", name="Cart-hash", explode=true,
     *          @OA\Schema(type="string"),
     *      ),
     *     summary="Clear user cart",
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", type="object",
     *                  ref="#/components/schemas/CartResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *              @OA\Property(property="code", title="Code", example=0),
     *          )
     *     ),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function clearCart(): JsonResponse
    {
        try {
            $cart = resolve(CartInterface::class);
            $cart->clear();

            return self::successJsonMessage(CartResource::make($cart));
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return self::errorJsonMessage($e->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/mobile/cart/to-wishlist",
     *     tags={"Cart"},
     *     security={{"Basic": {}}},
     *     @OA\Parameter(in="header", name="Cart-hash", explode=true,
     *          @OA\Schema(type="string"),
     *      ),
     *     summary="Add items from cart to user wishlist",
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function toWishlist(): JsonResponse
    {
        /** @var $user User */
        $user = Auth::user();

        try {
            $cart = resolve(CartInterface::class);

            $userService = resolve(UserService::class);

            $userService->massAddToWishlist($user, $cart->getProductIds());

            return self::successJsonMessage(
                __('cms-catalog::site.products.Products have been added to wishlist')
            );
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return self::errorJsonMessage($e->getMessage());
        }
    }
}
