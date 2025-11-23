<?php

namespace WezomCms\Orders\Http\Controllers\Api\V1;

use AntistressStore\CdekSDK2\Entity\Responses\CitiesResponse;
use AntistressStore\CdekSDK2\Entity\Responses\TariffListResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Log;
use Str;
use Throwable;
use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\Orders\Cart\CartItem;
use WezomCms\Orders\Contracts\CartInterface;
use WezomCms\Orders\Http\Requests\Api\V1\CitiesRequest;
use WezomCms\Orders\Http\Requests\Api\V1\DeliveryPointsRequest;
use WezomCms\Orders\Http\Requests\Api\V1\TariffsRequest;
use WezomCms\Orders\Http\Resources\V1\SDEK\CityResource;
use WezomCms\Orders\Http\Resources\V1\SDEK\DeliveryPointResource;
use WezomCms\Orders\Http\Resources\V1\SDEK\RegionResource;
use WezomCms\Orders\Http\Resources\V1\SDEK\TariffResource;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Services\SdekService;
use WezomCms\TelegramBot\Telegram;

class SDEKController extends ApiController
{
    public const CITIES_LIMIT = 200;

    public function __construct(private SdekService $SDEKService)
    {
        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/mobile/sdek/regions",
     *     tags={"Delivery"},
     *     summary="Get regions list",
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", type="array",
     *                  @OA\Items(ref="#/components/schemas/RegionResource")
     *              )
     *          )
     *     ),
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function regions(): JsonResponse
    {
        Telegram::info("ROUTE - /sdek/regions");
        try {
            $regions = $this->SDEKService->getRegions();

            return self::successJsonMessage(RegionResource::collection($regions));
        } catch (Throwable $e) {
            Log::error($e->getMessage());

            return self::errorJsonMessage($e->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/mobile/sdek/cities",
     *     tags={"Delivery"},
     *     summary="Get cities list",
     *
     *     @OA\Parameter(
     *         name="region",
     *         in="query",
     *         description="Код региона",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         description="Поисковый запрос",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Максимальное кол-во результатов (default - 200)",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", type="array",
     *                  @OA\Items(ref="#/components/schemas/CityResource")
     *              )
     *          )
     *     ),
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @param CitiesRequest $request
     * @return JsonResponse
     */
    public function cities(CitiesRequest $request): JsonResponse
    {

        Telegram::info("ROUTE - /sdek/cities");
        try {
            $cities = $this->SDEKService->getCities($request->get('region'));

            $query = $request->get('query');

            if ($query) {
                $query = Str::lower($query);

                $cities = $cities->filter(function (CitiesResponse $city) use ($query) {
                    return Str::contains(Str::lower($city->getCity()), $query);
                });
            }

            $limit = $request->get('limit', self::CITIES_LIMIT);

            return self::successJsonMessage(CityResource::collection($cities->take($limit)));
        } catch (Throwable $e) {
            Log::error($e->getMessage());

            return self::errorJsonMessage($e->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/mobile/sdek/delivery-points",
     *     tags={"Delivery"},
     *     summary="Get delivery points list",
     *
     *     @OA\Parameter(
     *         name="city",
     *         in="query",
     *         description="Код города",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", type="array",
     *                  @OA\Items(ref="#/components/schemas/DeliveryPointResource")
     *              )
     *          )
     *     ),
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @param DeliveryPointsRequest $request
     * @return JsonResponse
     */
    public function deliveryPoints(DeliveryPointsRequest $request): JsonResponse
    {
        Telegram::info("ROUTE - /sdek/delivery-points");
        try {
            $points = $this->SDEKService->getDeliveryPoints($request->get('city'));

            return self::successJsonMessage(DeliveryPointResource::collection($points));
        } catch (Throwable $e) {
            Log::error($e->getMessage());

            return self::errorJsonMessage($e->getMessage());
        }
    }

    /**
     * @OA\Post (
     *     path="/mobile/sdek/tariffs",
     *     tags={"Delivery"},
     *     summary="Get sdek tariffs list",
     *
     *     @OA\RequestBody(required=true,
     *           @OA\JsonContent(ref="#/components/schemas/TariffsRequest")
     *     ),
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", type="array",
     *                  @OA\Items(ref="#/components/schemas/TariffResource")
     *              )
     *          )
     *     ),
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @param TariffsRequest $request
     * @return JsonResponse|AnonymousResourceCollection
     */
    public function tariffs(TariffsRequest $request): JsonResponse|AnonymousResourceCollection
    {
        Telegram::info("ROUTE - /sdek/tariffs");

        try {
            $tariffs = $this->SDEKService->getDefaultTariff(
                $request->get('city_code'),
                $this->getOrdersData(),
            );
            /*$tariffs = $this->SDEKService->getTariffs(
                $request->get('city_code'),
                $request->get('postal_code'),
                $this->getOrdersData()
            );*/

            $this->setCartDeliveryData($tariffs->get('providers'));

            return self::successJsonMessage(TariffResource::collection($tariffs->get('sum')));
        } catch (Throwable $e) {
            Log::error($e->getMessage());

            return self::errorJsonMessage($e->getMessage());
        }
    }

    private function setCartDeliveryData(Collection $deliveries): void
    {
        $cart = app(CartInterface::class);

        $deliveryData = $deliveries
            ->map(function (Collection $providerData) {
                return $providerData->map(function (TariffListResponse $tariff) {
                    return $tariff->getDeliverySum();
                });
            })
            ->toArray();

        $cart->setDeliveryData($deliveryData);
    }

    private function getOrdersData(): array
    {
        $cart = app(CartInterface::class);

        return $cart->content()
            ->mapToGroups(function (CartItem $cartItem) {
                $product = $cartItem->getPurchaseItem();

                return [
                    $product->providerProfile->id => [
                        'city_code' => $product->providerProfile->city_code,
                        'weight' => $cartItem->getWeight(),
                        'length' => $cartItem->getLength(),
                        'width' => $cartItem->getWidth(),
                        'height' => $cartItem->getHeight(),
                    ],
                ];
            })
            ->toArray();
    }

    public function webhooks(Request $request): void
    {
        if ($request->get('type') !== 'ORDER_STATUS') {
            return;
        }

        $attributes = $request->get('attributes', []);
        /** @var Order $order */
        $order = Order::find(data_get($attributes, 'number'));

        if ($order) {
            $deliveryInfo = $order->deliveryInformation;
            $orderUuid = $request->get('uuid');
            if ($deliveryInfo->uuid === $orderUuid && $ttn = data_get($attributes, 'cdek_number')) {
                $deliveryInfo
                    ->setTtn($ttn)
                    ->addDeliveryStatus([
                        'status_code' => data_get($attributes, 'status_code'),
                        'code' => data_get($attributes, 'code'),
                        'status_date_time' => data_get($attributes, 'status_date_time'),
                    ])
                    ->save();
            }
        }
    }
}
