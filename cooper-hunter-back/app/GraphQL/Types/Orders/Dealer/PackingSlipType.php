<?php

namespace App\GraphQL\Types\Orders\Dealer;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Media\MediaType;
use App\Models\Orders\Dealer\PackingSlip;
use GraphQL\Type\Definition\Type;
use App\GraphQL\Types\Enums\Orders\Dealer;

class PackingSlipType extends BaseType
{
    public const NAME = 'dealerOrderPackingSlipType';
    public const MODEL = PackingSlip::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'guid' => [
                    'type' => Type::string(),
                ],
                'status' => [
                    'type' => Dealer\OrderStatusTypeEnum::nonNullType(),
                ],
                'number' => [
                    'type' => Type::string(),
                ],
                'tracking_number' => [
                    'type' => Type::string(),
                ],
                'tracking_company' => [
                    'type' => Type::string(),
                ],
                'shipped_at' => [
                    'type' => Type::string(),
                ],
                'dimensions' => [
                    'type' => DimensionsType::list()
                ],
                'media' => [
                    'type' => MediaType::list(),
                    'always' => 'id',
                    'alias' => 'media',
                ],
                'items' => [
                    'type' => PackingSlipItemType::list(),
                    'description' => 'Товары относящиеся к данному слипу'
                ],
                'serial_numbers' => [
                    'alias' => 'serialNumbers',
                    'type' => PackingSlipSerialNumberType::list()
                ],
                'invoice' => [
                    'type' => Type::string(),
                ],
                'invoice_at' => [
                    'type' => Type::string(),
                ],
                'invoice_file_link' => [
                    'type' => Type::string(),
                    'description' => 'ссылка на скачивание файла',
                    'selectable' => false,
                    'resolve' => fn (PackingSlip $model) => $model->getInvoiceFileStorageUrl()
                ],
                'tax' => [
                    'type' => Type::float(),
                    'description' => 'налог, пиходит от 1с'
                ],
                'shipping_price' => [
                    'type' => Type::float(),
                    'description' => 'стоимость доставки, пиходит от 1с'
                ],
                'total' => [
                    'type' => Type::float(),
                    'description' => 'общая сумма заказа, пиходит от 1с'
                ],
                'total_discount' => [
                    'type' => Type::float(),
                    'description' => 'общую сумма скидки, пиходит от 1с'
                ],
                'total_with_discount' => [
                    'type' => Type::float(),
                    'description' => 'общая сумма заказа со скидкой, пиходит от 1с'
                ],
                'files' => [
                    'type' => Type::listOf(OrderFileFromOnecType::type()),
                    'always' => 'files',
                    'selectable' => false,
                    'is_relation' => false,
                    'description' => 'массив файлов (название и линк), который пришли от 1с'
                ],
            ]
        );
    }
}
