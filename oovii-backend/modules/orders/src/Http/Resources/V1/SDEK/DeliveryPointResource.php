<?php

namespace WezomCms\Orders\Http\Resources\V1\SDEK;

use AntistressStore\CdekSDK2\Entity\Responses\DeliveryPointsResponse;
use AntistressStore\CdekSDK2\Entity\Responses\PhoneResponse;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Delivery point resource",
 *     description="Delivery point resource",
 * )
 */
class DeliveryPointResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var $model DeliveryPointsResponse */
        $model = $this;

        $phones = collect($model->getPhones())
            ->map(function (PhoneResponse $phone) {
                return $phone->getNumber();
            });

        return [
            'name' => $model->getName(),
            'code' => $model->getCode(),
            'location' => LocationResource::make($model->getLocation()),
            'work_time' => $model->getWorkTime(),
            'note' => $model->getNote(),
            'owner_code' => $model->getOwnerCode(),
            'nearest_station' => $model->getNearestStation(),
            'nearest_metro_station' => $model->getNearestMetroStation(),
            'site' => $model->getSite(),
            'email' => $model->getEmail(),
            'address_comment' => $model->getAddressComment(),
            // 'dimensions' => $model->getDimensions(),
            'phones' => $phones,
            'type' => $model->getType(),
            'have_cashless' => $model->getHaveCashless(),
            'have_cash' => $model->getHaveCash(),
            'allowed_cod' => $model->getAllowedCod(),
            'is_dressing_room' => $model->getIsDressingRoom(),
            'is_handout' => $model->getIsHandout(),
            'is_reception' => $model->getIsReception(),
            'weight_max' => $model->getWeightMax(),
            'weight_min' => $model->getWeightMin(),
        ];
    }

    /**
     * @OA\Property(property="name", title="Название", description="Название пункта выдачи", example="На Алтынсарина, 27"),
     * @OA\Property(property="code", title="Код", description="Код (идентификатор) пункта выдачи", example="ALM28"),
     * @OA\Property(property="location", title="Location", description="location", type="object", example={"city_code": 4756, "postal_code": "050031"}),
     * @OA\Property(property="work_time", title="Режим работы", description="Режим работы", example="Пн-Пт 10:00-20:00, Сб-Вс 10:00-18:00"),
     * @OA\Property(property="note", title="Примечание", description="Примечание по офису", example="В здании Каспи банка, вход с торца"),
     * @OA\Property(property="owner_code", title="Принадлежность офиса компании", description="Принадлежность офиса компании (СДЭК/InPost)", enum={"cdek", "InPost"}, example="cdek"),
     * @OA\Property(property="nearest_station", title="Ближайшая станция", description="Ближайшая станция/остановка транспорта", example="ул. Жандосова, пр-т. Алтынсарина"),
     * @OA\Property(property="nearest_metro_station", title="Ближайшая станция метро", description="Ближайшая станция метро", example=""),
     * @OA\Property(property="site", title="Ссылка на данный офис на сайте СДЭК", description="Ссылка на данный офис на сайте СДЭК", example=""),
     * @OA\Property(property="email", title="Email", description="Адрес электронной почты", example="zh.imanbekova@cdek.ru"),
     * @OA\Property(property="address_comment", title="Описание местоположения", description="Описание местоположения", example="В здании Каспи банка, вход с торца"),
     * @OA\Property(property="phones", title="Список телефонов", description="Список телефонов", type="array", @OA\Items(type="string", example="+79232478998")),
     * @OA\Property(property="type", title="Тип пункта выдачи", description="Тип ПВЗ: PVZ — склад СДЭК, POSTAMAT — постамат СДЭК", example="PVZ"),
     * @OA\Property(property="have_cashless", title="Есть безналичный расчет", description="Есть безналичный расчет", example=true),
     * @OA\Property(property="have_cash", title="Есть приём наличных", description="Есть приём наличных", example=true),
     * @OA\Property(property="allowed_cod", title="Разрешен наложенный платеж в ПВЗ", description="Разрешен наложенный платеж в ПВЗ", example=true),
     * @OA\Property(property="is_dressing_room", title="Есть ли примерочная", description="Есть ли примерочная", example=true),
     * @OA\Property(property="is_handout", title="	Является пунктом выдачи", description="	Является пунктом выдачи", example=true),
     * @OA\Property(property="is_reception", title="Является пунктом приёма", description="Является пунктом приёма", example=true),
     * @OA\Property(property="weight_max", title="Максимальный вес", description="Максимальный вес (в кг.)", example=5),
     * @OA\Property(property="weight_min", title="Минимальный вес", description="Минимальный вес (в кг.)", example=null),
     */
}
