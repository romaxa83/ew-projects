<?php

namespace App\Services\Histories;

use App\Models\DiffableInterface;
use App\Models\Users\DriverInfo;
use App\Models\Users\DriverLicense;
use App\Models\Users\User;
use Swaggest\JsonDiff\JsonDiff;

class UserHistoryHandler implements HistoryHandlerInterface
{
    private array $origin = [];
    private array $dirty = [];
    protected ?DiffableInterface $userOld = null;
    protected ?DiffableInterface $userNew = null;

    public function setOrigin(?DiffableInterface $diffable): self
    {
        $this->origin = $diffable ? $diffable->getAttributesForDiff() : [];
        $this->userOld = $diffable ?clone $diffable : null;

        return $this;
    }

    public function setDirty(?DiffableInterface $diffable): self
    {
        $this->dirty = $diffable ? $diffable->getAttributesForDiff() : [];
        $this->userNew = $diffable ?? null;

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
            'created_at',
            'updated_at',
            'second_name',
            'password',
            'remember_token',
            'deleted_at',
            'language',
            'fcm_token',
            'broker_id',
            'carrier_id',

            'driverInformation.driver_id',

            'driverLicense.driver_id',
            'driverLicense.created_at',
            'driverLicense.updated_at',
            'driverLicense.type',
            'driverLicense.issuing_country',

            'previousDriverLicense.driver_id',
            'previousDriverLicense.created_at',
            'previousDriverLicense.updated_at',
            'previousDriverLicense.type',

            'attachments.created_at',
            'attachments.updated_at',
            'attachments.file_name',
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
                $keysForValid = array_filter($keys, function ($n) {
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
        if($oldRelation = $this->mapsAndReplacingKeyWithValue(implode('.', $keys), $this->userOld))
        {
            $newRelation = $this->mapsAndReplacingKeyWithValue(implode('.', $keys), $this->userNew);

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
            'first_name',
            'last_name',
            'phone',
            'phone_extension',
            'email',
            'phones.number',
            'phones.extension',
            'can_check_orders',
            'role',
            'owner_id',

            'comments.comment',

            'driverInformation.driver_rate',
            'driverInformation.notes',
            'driverInformation.medical_card_number',
            'driverInformation.medical_card_issuing_date',
            'driverInformation.medical_card_expiration_date',
            'driverInformation.mvr_reported_date',
            'driverInformation.has_company',
            'driverInformation.company_name',
            'driverInformation.company_ein',
            'driverInformation.company_address',
            'driverInformation.company_city',
            'driverInformation.company_zip',

            'driverInformation.' . DriverInfo::ATTACHED_MVR_FILED_NAME . '.name',
            'driverInformation.' . DriverInfo::ATTACHED_MEDICAL_CARD_FILED_NAME . '.name',

            'attachments.name',

            'driverLicense.license_number',
            'driverLicense.issuing_date',
            'driverLicense.expiration_date',
            'driverLicense.issuing_state_id',
            'driverLicense.category',
            'driverLicense.category_name',
            'driverLicense.' . DriverLicense::ATTACHED_DOCUMENT_FILED_NAME . '.name',

            'previousDriverLicense.license_number',
            'previousDriverLicense.issuing_date',
            'previousDriverLicense.expiration_date',
            'previousDriverLicense.issuing_country',
            'previousDriverLicense.issuing_state_id',
            'previousDriverLicense.category',
            'previousDriverLicense.category_name',
            'previousDriverLicense.' . DriverLicense::ATTACHED_DOCUMENT_FILED_NAME . '.name',

            'tags.name',
        ];

        return in_array($key, $keys, true);
    }

    private function mapsAndReplacingKeyWithValue(string $key, ?User $user): array
    {
        if (!$user) {
            return [];
        }

        $driverLicense = $user->driverLicenses->where('type', DriverLicense::TYPE_CURRENT)->first();
        $previousDriverLicense = $user->driverLicenses->where('type', DriverLicense::TYPE_PREVIOUS)->first();

        $maps = [
            'owner_id' =>  [
                'owner_id' => $user->owner->full_name ?? null,
            ],
            'driverInformation.medical_card_issuing_date' =>  [
                'medical_card_issuing_date' => $user->driverInfo && $user->driverInfo->medical_card_issuing_date ?
                    $user->driverInfo->medical_card_issuing_date->format('m/d/Y')
                    : null,
            ],
            'driverInformation.medical_card_expiration_date' =>  [
                'medical_card_issuing_date' => $user->driverInfo && $user->driverInfo->medical_card_expiration_date ?
                    $user->driverInfo->medical_card_expiration_date->format('m/d/Y')
                    : null,
            ],
            'driverInformation.mvr_reported_date' =>  [
                'medical_card_issuing_date' => $user->driverInfo && $user->driverInfo->mvr_reported_date ?
                    $user->driverInfo->mvr_reported_date->format('m/d/Y')
                    : null,
            ],
            'driverLicense.issuing_date' =>  [
                'issuing_date' => $driverLicense && $driverLicense->issuing_date ?
                    $driverLicense->issuing_date->format('m/d/Y')
                    : null,
            ],
            'driverLicense.expiration_date' =>  [
                'expiration_date' => $driverLicense && $driverLicense->expiration_date ?
                    $driverLicense->expiration_date->format('m/d/Y')
                    : null,
            ],
            'driverLicense.issuing_state_id' =>  [
                'issuing_state_id' => $driverLicense && $driverLicense->issuing_state_id ?
                    $driverLicense->issuingState->name
                    : null,
            ],
            'previousDriverLicense.issuing_date' =>  [
                'issuing_date' => $previousDriverLicense && $previousDriverLicense->issuing_date ?
                    $previousDriverLicense->issuing_date->format('m/d/Y')
                    : null,
            ],
            'previousDriverLicense.expiration_date' =>  [
                'expiration_date' => $previousDriverLicense && $previousDriverLicense->expiration_date ?
                    $previousDriverLicense->expiration_date->format('m/d/Y')
                    : null,
            ],
            'previousDriverLicense.issuing_state_id' =>  [
                'issuing_state_id' => $previousDriverLicense && $previousDriverLicense->issuing_state_id ?
                    $previousDriverLicense->issuingState->name
                    : null,
            ],
        ];

        return $maps[$key] ?? [];
    }

    private function mapsAndReplacingKeyWithValueForCollection(string $key, $id): array
    {
        return [];
    }
}
