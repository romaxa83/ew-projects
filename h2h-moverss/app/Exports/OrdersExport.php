<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\{FromQuery,
    Exportable,
    WithColumnWidths,
    WithHeadings,
    WithMapping
};

ini_set('memory_limit', '1G');

class OrdersExport implements FromQuery, WithHeadings, WithMapping, WithColumnWidths
{
    use Exportable;

    public function query()
    {
        return Order::query()
            ->where('division_id', 1)
            ->whereHas('client')
            ->with([
                'client:id,name,lname',
                'client.emails' => function ($q) {
                    return $q
                        ->orderBy('is_primary', 'desc')
                        ->orderBy('sort', 'asc')
                        ->select('id', 'client_id', 'value');
                },
                'client.phones' => function ($q) {
                    return $q
                        ->orderBy('is_primary', 'desc')
                        ->orderBy('sort', 'asc')
                        ->select('id', 'client_id', 'value');
                },
                'waypoints' => function ($q) {
                    return $q
                        ->orderBy('sort');
                },
                'works' => function ($q) {
                    $q->orderBy('start_date');
                },
                'works.workTypes',
            ])
            ->whereYear('created_at', '>=', '2023');
    }

    public function map($order): array
    {
        $phone = ($order->client->phones[0]->value ?? '');
        if ($phone) {
            $phone = '+'.$phone;
        }

        $OriginAddress = $OriginAddressAp = $OriginAddressCity = $OriginAddressZip = $OriginAddressState = '';
        if (isset($order->waypoints[0])) {
            $OriginAddress = $order->waypoints[0]->address;
            $OriginAddressAp = $order->waypoints[0]->ap;
            $OriginAddressCity = $order->waypoints[0]->city;
            $OriginAddressState = $order->waypoints[0]->state;
            $OriginAddressZip = $order->waypoints[0]->zip;
        }

        $DstAddress = $DstAddressAp = $DstAddressCity = $DstAddressZip = $DstAddressState = '';
        if (isset($order->waypoints[1]) && $lastWP = $order->waypoints->last()) {
            $DstAddress = $lastWP->address;
            $DstAddressAp = $lastWP->ap;
            $DstAddressCity = $lastWP->city;
            $DstAddressState = $lastWP->state;
            $DstAddressZip = $lastWP->zip;
        }

        if ($volume = $order->sizing_volume) {
            $volume .= ' CuFT';
        }
        if ($weight = $order->sizing_weight) {
            $weight .= ' lb';
        }

        $MoveDate = $order->works
            ->whereNotNull('start_date')
            ->filter(function ($item) {
                return $item->workTypes;
            })
            ->filter(function ($item) {
                return $item->workTypes->contains('id', '1');
            })
            ->first();
        $MoveDate = $MoveDate->start_date ?? '';

        $isBooked = $order->works->where('in_dispatch', 1)->count() ? 'Y' : 'N';

        return [
            $order->id.' ('.$order->created_at->format('Y-m-d').')',
            $order->client->name,
            $order->client->lname,
            $order->client->emails[0]->value ?? '',
            $phone,
            $OriginAddress,
            $OriginAddressAp,
            $OriginAddressCity,
            $OriginAddressState,
            $OriginAddressZip,
            $MoveDate,
            $DstAddress,
            $DstAddressAp,
            $DstAddressCity,
            $DstAddressState,
            $DstAddressZip,
            $volume,
            $weight,
            $isBooked,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 15,
            'C' => 15,
            'D' => 20,
            'E' => 15,
            'F' => 30, // Origin Address
            'G' => 10, // OriginAptOrSuite
            'H' => 15, // OriginCity
            'I' => 10, // OriginState
            'J' => 10, // OriginZipcode
            'K' => 15, // $MoveDate
            'L' => 30, // Destination Address
            'M' => 10, // DestinationAptOrSuite
            'N' => 15, // Destination City
            'O' => 10, // Destination State
            'P' => 10, // Destination Zipcode
            'Q' => 10, // $volume
            'R' => 10, // Weight
            'S' => 10, // Is this a Booked Move
        ];
    }

    public function headings(): array
    {
        return [
            '#',
            'FirstName',
            'LastName',
            'Email Address',
            'Phone',
            'Origin Address',
            'OriginAptOrSuite',
            'OriginCity',
            'OriginState',
            'Origin Zipcode',
            'Move Date',
            'Destination Address',
            'DestinationAptOrSuite',
            'Destination City',
            'Destination State',
            'Destination Zipcode',
            'Volume',
            'Weight',
            'Is this a Booked Move (Y/N)',
        ];
    }
}
