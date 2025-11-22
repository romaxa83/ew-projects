<?php

namespace App\Exports\BodyShop;

use App\Models\BodyShop\Inventories\Inventory;
use App\Models\Orders\Order;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InventoryExport  implements FromCollection, WithMapping, WithHeadings
{
    private Collection $items;

    public function __construct(
        Collection $items
    )
    {
        $this->items = $items;
    }



    public function headings(): array
    {
        return [
            [
                'List of parts as at ' . CarbonImmutable::now()->format('d/m/Y')
            ],
            [
                'Name',
                'Number',
                'Category',
                'Price',
                'Quantity',
                'Additional details',
            ],
        ];
    }

    public function collection(): Collection
    {
        return $this->items;
    }

    /**
     * @var Order $order
     */
    public function map($model): array
    {
        /** @var $model Inventory */

        return [
            [
                $model->name,
                $model->stock_number,
                $model->category->name ?? null,
                $model->price_retail,
                $model->quantity . ' ' . $model->unit->name,
                strip_tags($model->notes)
            ]
        ];
    }
}
