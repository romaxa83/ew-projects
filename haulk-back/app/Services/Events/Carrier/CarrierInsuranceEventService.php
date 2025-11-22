<?php


namespace App\Services\Events\Carrier;


use App\Broadcasting\Events\Carrier\UpdateCarrierInsuranceBroadcast;
use App\Events\ModelChanged;
use App\Models\Saas\Company\CompanyInsuranceInfo;
use App\Services\Events\EventService;

class CarrierInsuranceEventService extends EventService
{
    public const ACTION_PREFIX_ADD_PHOTO = '_add_photo';
    public const ACTION_PREFIX_DELETE_PHOTO = '_delete_photo';

    private const HISTORY_MESSAGE_INSURANCE_UPDATE = 'history.carrier_insurance_updated';
    private const HISTORY_MESSAGE_INSURANCE_PHOTO_ADDED = 'history.carrier_insurance_photo_added';
    private const HISTORY_MESSAGE_INSURANCE_PHOTO_DELETED = 'history.carrier_insurance_photo_deleted';

    private ?CompanyInsuranceInfo $insurance;

    public function __construct(CompanyInsuranceInfo $insuranceInfo)
    {
        $this->insurance = $insuranceInfo;
    }

    private function getHistoryMessage(): ?string
    {
        switch ($this->action) {
            case self::ACTION_UPDATE:
                return self::HISTORY_MESSAGE_INSURANCE_UPDATE;
            case self::ACTION_UPDATE . self::ACTION_PREFIX_ADD_PHOTO:
                return self::HISTORY_MESSAGE_INSURANCE_PHOTO_ADDED;
            case self::ACTION_UPDATE . self::ACTION_PREFIX_DELETE_PHOTO:
                return self::HISTORY_MESSAGE_INSURANCE_PHOTO_DELETED;
        }
        return null;
    }

    private function getHistoryMeta(): array
    {
        $meta = [
            'role' => $this->user->getRoleName(),
            'full_name' => $this->user->full_name,
            'email' => $this->user->email,
            'user_id' => $this->user->id,
        ];

        return $meta;
    }

    private function setHistory(): void
    {
        event(
            new ModelChanged(
                $this->insurance,
                $this->getHistoryMessage(),
                $this->getHistoryMeta(),
            )
        );
    }

    public function update(?string $prefix = null): CarrierInsuranceEventService
    {
        $this->action = self::ACTION_UPDATE . ($prefix !== null ? $prefix : '');

        $this->setHistory();

        return $this;
    }

    public function broadcast(): CarrierInsuranceEventService
    {
        event(new UpdateCarrierInsuranceBroadcast($this->insurance->company->id));

        return $this;
    }
}
