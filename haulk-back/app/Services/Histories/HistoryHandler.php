<?php

namespace App\Services\Histories;

use App\Models\Contacts\Contact;
use App\Models\DiffableInterface;
use App\Models\Orders\Expense;
use App\Models\Orders\Payment;
use App\Models\Orders\Vehicle;
use App\Services\TimezoneService;
use Swaggest\JsonDiff\JsonDiff;

class HistoryHandler implements HistoryHandlerInterface
{
    private array $origin = [];
    private array $dirty = [];
    protected DiffableInterface $orderOld;
    protected DiffableInterface $orderNew;

    public function setOrigin(DiffableInterface $diffable): self
    {
        $this->origin = $diffable->getAttributesForDiff();
        $this->orderOld = clone $diffable;

        if ($diffable->payment) {
            $this->orderOld->payment = clone $diffable->payment;
        }

        return $this;
    }

    public function setDirty(DiffableInterface $diffable): self
    {
        $this->dirty = $diffable->getAttributesForDiff();
        $this->orderNew = $diffable;

        return $this;
    }

    public function start(): array
    {
        $jsonDiff = new JsonDiff(
            $this->origin,
            $this->dirty,
            JsonDiff::COLLECT_MODIFIED_DIFF + JsonDiff::TOLERATE_ASSOCIATIVE_ARRAYS
        );

        $comparisons = [];
        $keys = [];
        foreach ($jsonDiff->getModifiedDiff() as $item) {
            $flatStructure = [str_replace('/', '.', trim($item->path, '/')) => $item->new ?? null];

            if (!$flatStructure) {
                continue;
            }

            $array = array_undot($flatStructure);
            $this->generateComparisonsChanges($comparisons, $array, $keys,$item->original ?? null,);
        }

        if ($added = $jsonDiff->getAdded()) {
            $this->generateComparisons($comparisons, $added);
        }
        if ($removed = $jsonDiff->getRemoved()) {
            $this->generateComparisons($comparisons, $removed, false);
        }

        return $comparisons;
    }

    private function skipped($key): bool
    {
        $keys = [
            'is_billed',
            'created_at',
            'updated_at',
            'old_values',
            'pickup_full_name',
            'delivery_full_name',
            'shipper_full_name',
            'calculated_status',

            'vehicles.updated_at',
            'expenses.updated_at',
            'bonuses.updated_at',
            'pickup_contact.working_hours',
            'delivery_contact.working_hours',
            'shipper_contact.working_hours',

            'payment.old_values',
            'payment.planned_date',
            'payment.updated_at',
            'payment.driver_pay',

            'attachments.file_name',
            'attachments.updated_at',
        ];

        return in_array($key, $keys, true);
    }

    private function generateComparisons(&$comparisons, $attributes, $added = true, &$keys = []): void
    {
        foreach ($attributes as $key => $attribute) {
            if (!$attribute) {
                continue;
            }
            $keys[] = $key;
            if (is_object($attribute) || is_array($attribute)) {
                $this->generateComparisons($comparisons, $attribute, $added, $keys);
            } else {
                $keysForValid = array_filter($keys, function ($n){
                    return !is_numeric($n);
                });

                if($this->mapsValidKeys(implode('.', $keysForValid)))
                {
                    if(!$added) {
                        $this->setAttribute($comparisons, null, $keys, $keysForValid, $attribute, 'removed');
                    } else {
                        $this->setAttribute($comparisons, $attribute, $keys, $keysForValid, null, 'added');
                    }
                }
            }

            array_pop($keys);
        }
    }

    private function generateComparisonsChanges(&$comparisons, $attributes, &$keys, $old): void
    {
        foreach ($attributes as $key => $attribute) {
            if ($attribute === $old) {
                continue;
            }
            $keys[] = $key;
            if ($attribute !== null && (is_object($attribute) || is_array($attribute))) {
                $this->generateComparisonsChanges($comparisons, $attribute, $keys, $old);
            } else {
                $keysForValid = array_filter($keys, function ($n){
                    return !is_numeric($n);
                });

                if (!$this->skipped(implode('.', $keysForValid))) {
                    $this->setAttribute($comparisons, $attribute, $keys, $keysForValid, $old);
                }
            }

            array_pop($keys);
        }
    }

    private function setAttribute(&$comparisons, $attribute, $keys, $newKeys, $old, $type = 'updated'): void
    {
        if($oldRelation = $this->mapsAndReplacingKeyWithValue(implode('.', $keys), $this->orderOld))
        {
            $newRelation = $this->mapsAndReplacingKeyWithValue(implode('.', $keys), $this->orderNew);

            unset($keys[array_key_last($keys)]);

            $newKey = array_key_last($newRelation);
            $keys[] = $newKey;

            $comparisons[implode('.', $keys)] = [
                'new' => $newRelation[$newKey] ?? null,
                'old' => $oldRelation[$newKey] ?? null,
                'type' => $type
            ];
        } elseif($oldRelation = $this->mapsAndReplacingKeyWithValueForCollection(implode('.', $newKeys), $old)){
            $newRelation = $this->mapsAndReplacingKeyWithValueForCollection(implode('.', $newKeys), $attribute);

            unset($keys[array_key_last($keys)]);

            $newKey = array_key_last($newRelation);
            $keys[] = $newKey;

            $comparisons[implode('.', $keys)] = [
                'new' => $newRelation[$newKey] ?? null,
                'old' => $oldRelation[$newKey] ?? null,
                'type' => $type
            ];
        } else {
            $comparisons[implode('.', $keys)] = [
                'old' => $old,
                'new' => $attribute,
                'type' => $type
            ];
        }
    }

    private function mapsValidKeys($key): bool
    {
        $keys = [
            'comments.comment',

            'paymentStages.amount',
            'paymentStages.payment_date',
            'paymentStages.payer',
            'paymentStages.method_id',
            'paymentStages.uship_number',
            'paymentStages.reference_number',
            'paymentStages.notes',

            'vehicles.vin',
            'vehicles.stock_number',
            'vehicles.make',
            'vehicles.model',
            'vehicles.year',
            'vehicles.color',
            'vehicles.type_id',
            'vehicles.inop',
            'vehicles.enclosed',

            'attachments.name',

            'bonuses.price',
            'bonuses.to',
            'bonuses.type',

            'expenses.price',
            'expenses.to',
            'expenses.type_id',
            'expenses.date',
            //'expenses.receipt',
            'expenses.receipt.name',
            //'expenses.expenses',
            //'expenses.expenses.name',

            'shipper_contact.phones.name',
            'shipper_contact.phones.number',
            'shipper_contact.phones.extension',
            'shipper_contact.phones.notes',

            'pickup_contact.phones.name',
            'pickup_contact.phones.number',
            'pickup_contact.phones.extension',
            'pickup_contact.phones.notes',

            'delivery_contact.phones.name',
            'delivery_contact.phones.number',
            'delivery_contact.phones.extension',
            'delivery_contact.phones.notes',

            'tags',
        ];
        return in_array($key, $keys, true);
    }

    private function mapsAndReplacingKeyWithValue($key, $order): array
    {
        $timezones = resolve(TimezoneService::class)->getTimezonesArr()->pluck('title', 'timezone')->toArray();

        $maps = [
            'dispatcher_id' =>  [
                'dispatcher_id' => $order->dispatcher->full_name ?? null
            ],
            'driver_id' => [
                'driver_id' => $order->driver->full_name ?? null
            ],

            'payment.customer_payment_method_id' => [
                'customer_payment_method_id' => ($order->payment && $order->payment->customer_payment_method_id) ? Payment::ALL_METHODS[$order->payment->customer_payment_method_id] : null,
            ],
            'payment.broker_payment_method_id' => [
                'broker_payment_method_id' => ($order->payment && $order->payment->broker_payment_method_id) ? Payment::ALL_METHODS[$order->payment->broker_payment_method_id] : null,
            ],
            'payment.broker_fee_method_id' => [
                'broker_fee_method_id' => ($order->payment && $order->payment->broker_fee_method_id) ? Payment::ALL_METHODS[$order->payment->broker_fee_method_id] : null,
            ],
            'payment.driver_payment_method_id' => [
                'driver_payment_method_id' => ($order->payment && $order->payment->driver_payment_method_id) ? Payment::ALL_METHODS[$order->payment->driver_payment_method_id] : null,
            ],

            'pickup_contact.state_id' => [
                'state_id' => isset($order->pickup_contact['state_id']) ? (new Contact($order->pickup_contact))->state->name : null
            ],
            'pickup_contact.type_id' => [
                'type_id' => isset($order->pickup_contact['type_id']) ? Contact::CONTACT_TYPES[$order->pickup_contact['type_id']] : null
            ],
            'pickup_contact.timezone' => [
                'timezone' => isset($order->pickup_contact['timezone'], $timezones[$order->pickup_contact['timezone']])
                    ? $timezones[$order->pickup_contact['timezone']]
                    : null
            ],

            'delivery_contact.state_id' => [
                'state_id' => isset($order->delivery_contact['state_id']) ? (new Contact($order->delivery_contact))->state->name : null
            ],
            'delivery_contact.type_id' => [
                'type_id' => isset($order->delivery_contact['type_id']) ? Contact::CONTACT_TYPES[$order->delivery_contact['type_id']] : null
            ],
            'delivery_contact.timezone' => [
                'timezone' => isset($order->delivery_contact['timezone'], $timezones[$order->delivery_contact['timezone']])
                    ? $timezones[$order->delivery_contact['timezone']]
                    : null
            ],

            'shipper_contact.state_id' => [
                'state_id' => isset($order->shipper_contact['state_id']) ? (new Contact($order->shipper_contact))->state->name : null
            ],
            'shipper_contact.type_id' => [
                'type_id' => isset($order->shipper_contact['type_id']) ? Contact::CONTACT_TYPES[$order->shipper_contact['type_id']] : null
            ],
            'shipper_contact.timezone' => [
                'timezone' => isset($order->shipper_contact['timezone'], $timezones[$order->shipper_contact['timezone']])
                    ? $timezones[$order->shipper_contact['timezone']]
                    : null
            ],
        ];

        return $maps[$key] ?? [];
    }

    private function mapsAndReplacingKeyWithValueForCollection($key, $id): array
    {
        $maps = [
            'expenses.type_id' => [
                'type_id' => Expense::EXPENSE_TYPES[$id] ?? null
            ],
            'vehicles.type_id' => [
                'type_id' => Vehicle::VEHICLE_TYPES[$id] ?? null
            ],
            'paymentStages.method_id' => [
                'method_id' => Payment::ALL_METHODS[$id] ?? null
            ]
        ];

        return $maps[$key] ?? [];
    }
}
