<?php

namespace App\Exports;

use App\Models\Contacts\Contact;
use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrderExport implements FromCollection, WithMapping, WithHeadings
{
    private Collection $items;

    public function __construct(Collection $items)
    {
        $this->items = $items;
    }

    public function headings(): array
    {
        return [
            [
                'Load', '', '', '', '', '', '', '',
                'Origin', '', '',
                'Destination', '', '', '','', '',
                'Customer will pay to carrier', '', '',
                'Broker will pay to carrier', '', '', '',
                'Carrier will pay to broker', '', '', '',
                'Shipper', '',
                'Driver\'s data', '', '',
            ],
            [
                //Load
                'Driver',
                'Order',
                'Dispatcher',
                'Year',
                'Make',
                'Model',
                'INOP',
                'Enclosed',
                //Origin
                'State, zip code',
                'Pickup date (planned)',
                'Pickup date (actual)',
                //Destination
                'State, zip code',
                'Delivery date (planned)',
                'Delivery date (actual)',
                'Company',
                'Order date (created)',
                'Order number',
                //Customer will pay to carrier
                'Payment method',
                'Amount',
                'Location',
                //Broker will pay to carrier
                'Payment method',
                'Amount',
                'Number of days',
                'Payment terms begin',
                //Carrier will pay to broker
                'Payment method',
                'Amount',
                'Number of days',
                'Payment terms begin',
                //Shipper
                'Name',
                'Agreement',
                //Driver's data
                'Payment method',
                'Amount',
                'Comment if payment is not received'
            ]
        ];
    }

    public function collection(): Collection
    {
        return $this->items;
    }

    /**
     * @var Order $order
     */
    public function map($order): array
    {
        $firstVehicle = true;
        $data = [];
        $pickupContact = new Contact($order->pickup_contact ?: []);
        $deliveryContact = new Contact($order->delivery_contact ?: []);

        foreach ($order->vehicles as $vehicle) {
            if ($firstVehicle) {
                $data[] = [
                    //Order
                    $order->driver ? $order->driver->full_name : '',
                    $order->load_id,
                    $order->dispatcher ? $order->dispatcher->full_name : '',
                    $vehicle->year ?? '',
                    $vehicle->make ?? '',
                    $vehicle->model ?? '',
                    $vehicle->inop ? 'Yes' : 'No',
                    $vehicle->enclosed ? 'Yes' : 'No',

                    //Origin
                    trim(sprintf('%s %s', $pickupContact->state->state_short_name ?? '', $pickupContact->zip ?? '')),
                    $order->pickup_date
                        ? sprintf(
                            '%s (%s - %s)',
                            date('m/d/Y', $order->pickup_date),
                            $order->pickup_time['from'] ?? '---',
                            $order->pickup_time['to'] ?? '---'
                        )
                        : '',
                    $this->formatDate(
                        $order->pickup_date_actual,
                        $pickupContact->timezone ?? $order->user->getCompany()->timezone
                    ),

                    //Destination
                    trim(sprintf('%s %s', $deliveryContact->state->state_short_name ?? '', $deliveryContact->zip ?? '')),
                    $order->delivery_date
                        ? sprintf(
                            '%s (%s - %s)',
                            date('m/d/Y', $order->delivery_date),
                            $order->delivery_time['from'] ?? '---',
                            $order->delivery_time['to'] ?? '---'
                        )
                        : '',
                    $this->formatDate(
                        $order->delivery_date_actual,
                        $deliveryContact->timezone ?? $order->user->getCompany()->timezone
                    ),
                    $order->user->getCompany()->name,
                    $this->formatDate($order->created_at->timestamp),
                    $order->load_id,

                    //Customer will pay to carrier
                    $order->payment ? Payment::ALL_METHODS[$order->payment->customer_payment_method_id] ?? '' : '',
                    $this->formatPrice($order->payment->customer_payment_amount ?? null),
                    $order->payment->customer_payment_location ?? '',

                    //Broker will pay to carrier
                    $order->payment ? Payment::ALL_METHODS[$order->payment->broker_payment_method_id] ?? '' : '',
                    $this->formatPrice($order->payment->broker_payment_amount ?? null),
                    $this->formatNumberOfDays($order->payment->broker_payment_days ?? null),
                    $order->payment->broker_payment_begins ?? '',

                    //Carrier will pay to broker
                    $order->payment ? Payment::ALL_METHODS[$order->payment->broker_fee_method_id] ?? '' : '',
                    $this->formatPrice($order->payment->broker_fee_amount ?? null),
                    $this->formatNumberOfDays($order->payment->broker_fee_days ?? null),
                    $order->payment->broker_fee_begins ?? '',

                    //Shipper
                    $order->shipper_contact['full_name'] ?? '',
                    $order->load_id,

                    //Driver's data
                    $order->payment ? $this->resolveDriverPaymentMethodField($order->payment) : '',
                    $this->formatPrice($order->payment->driver_payment_amount ?? null),
                    $order->payment ? $order->payment->driver_payment_comment ?? '' : '',
                ];
                $firstVehicle = false;
            } else {
                $data[] = [
                    '',
                    '',
                    '',
                    $vehicle->year ?? '',
                    $vehicle->make ?? '',
                    $vehicle->model ?? '',
                    $vehicle->inop ? 'Yes' : 'No',
                    $vehicle->enclosed ? 'Yes' : 'No',
                ];
            }
        }

        return $data;
    }

    private function formatDate(?int $timestamp, ?string $timezone = null): string
    {
        if (is_null($timestamp)) {
            return '';
        }

        $date = Carbon::createFromTimestamp($timestamp);

        if ($timezone) {
            $date->setTimezone($timezone);
        }

        return $date->format('m/d/Y g:i A T');
    }

    private function formatPrice(?float $price): string
    {
        return $price ? '$' . $price : '';
    }

    private function formatNumberOfDays(?int $days): string
    {
        if (is_null($days)) {
            return '';
        }

        if ($days === 0) {
            return 'Immediately';
        }

        return (string)$days;
    }

    private function resolveDriverPaymentMethodField(Payment $payment): string
    {
        if ($payment->driver_payment_method_id) {
            return Payment::ALL_METHODS[$payment->driver_payment_method_id] ?? '';
        }

        if ($payment->driver_payment_amount) {
            return 'Cash';
        }

        if (!empty($payment->getFirstMedia(Order::DRIVER_PAYMENT_COLLECTION_NAME))) {
            return 'Check';
        }

        if ($payment->driver_payment_uship_code) {
            return 'Uship';
        }

        if ($payment->driver_payment_comment) {
            return 'Not received';
        }

        return '';
    }
}
