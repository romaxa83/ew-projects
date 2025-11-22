<?php

namespace App\Exports\GPS;

use App\Exports\BaseExport;
use App\Models\GPS\History;
use App\Models\Orders\Order;
use App\Models\Saas\Company\Company;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class HistoryExport extends BaseExport implements FromCollection, WithMapping, WithHeadings
{
    private Collection $items;
    private Company $company;
    private ?string $unitNumber;
    private ?string $date;
    private ?string $driverNames;

    public function __construct(
        Collection $items,
        Company $company,
        array $filters
    )
    {
        $this->items = $items;

        $this->company = $company;

        $this->unitNumber = $this->getUnitNumbers();
        $this->driverNames = $this->getDriverNames();
        $this->date = $this->getDateStr($filters);
    }



    public function headings(): array
    {
        return [
            [
                'Vehicle driving history unit #: ' . $this->unitNumber,
                null,
                null,
                null,
            ],
            [
                'Driver: ' . $this->driverNames,
                null,
                null,
                null,
            ],
            [
                'Date: ' . $this->date,
                null,
                null,
                null,
            ],
            [
                null,
                null,
                null,
                null,
            ],
            [
                'Time',
                'Speed',
                'Heading',
                'Event',
                'Driver name',
                'Longitude',
                'Latitude',
                'Alert',
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
        /** @var $model History */

        return [
            [
                $this->formatDateForTracking(
                    $model->received_at->timestamp,
                    $this->company->timezone
                ),
                $model->speed,
                $model->heading,
                remove_underscore($model->event_type),
                $model->driver->full_name ?? null,
                $model->longitude,
                $model->latitude,
                $this->getAlertAsStr($model)
            ]
        ];
    }

    private function getUnitNumbers(): ?string
    {
        $truck = array_unique($this->items->pluck('truck.unit_number')->toArray());
        $trailer = array_unique($this->items->pluck('trailer.unit_number')->toArray());

        $unitNumber = array_merge($truck, $trailer);

        return implode(', ', array_diff($unitNumber, [null]));
    }

    private function getDateStr(array $filters): ?string
    {
        $msg = data_get($filters, 'date_from');

        return $msg;
    }

    private function getDriverNames(): ?string
    {
        $names = $this->items->pluck('driver.full_name')->toArray();

        return implode(', ', array_diff($names, [null]));
    }

    protected function getAlertAsStr(History $model): string
    {
        return remove_underscore(
            implode(', ',
                array_unique($model->alerts->pluck('alert_type')->toArray())
            )
        );
    }
}

