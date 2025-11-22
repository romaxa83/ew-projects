<?php


namespace App\Services\Events\Carrier;


use App\Broadcasting\Events\Carrier\DeleteCarrierBroadcast;
use App\Broadcasting\Events\Carrier\UpdateCarrierBroadcast;
use App\Events\ModelChanged;
use App\Models\Saas\Company\Company;
use App\Services\Events\EventService;

class CarrierEventService extends EventService
{
    public const ACTION_PREFIX_ADD_PHOTO = '_add_photo';
    public const ACTION_PREFIX_DELETE_PHOTO = '_delete_photo';
    public const ACTION_PREFIX_ADD_W9 = '_add_w9';
    public const ACTION_PREFIX_DELETE_W9 = '_delete_w9';
    public const ACTION_PREFIX_ADD_USDOT = '_add_usdot';
    public const ACTION_PREFIX_DELETE_USDOT = '_delete_usdot';

    private const HISTORY_MESSAGE_CARRIER_UPDATE = 'history.carrier_updated';
    private const HISTORY_MESSAGE_CARRIER_INFO_PHOTO_ADDED = 'history.carrier_info_photo_added';
    private const HISTORY_MESSAGE_CARRIER_INFO_PHOTO_DELETED = 'history.carrier_info_photo_deleted';
    private const HISTORY_MESSAGE_CARRIER_W9_PHOTO_ADDED = 'history.carrier_w9_photo_added';
    private const HISTORY_MESSAGE_CARRIER_W9_PHOTO_DELETED = 'history.carrier_w9_photo_deleted';
    private const HISTORY_MESSAGE_CARRIER_USDOT_PHOTO_ADDED = 'history.carrier_usdot_photo_added';
    private const HISTORY_MESSAGE_CARRIER_USDOT_PHOTO_DELETED = 'history.carrier_usdot_photo_deleted';

    private Company $company;

    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    private function getHistoryMessage(): ?string
    {
        switch ($this->action) {
            case self::ACTION_UPDATE:
                return self::HISTORY_MESSAGE_CARRIER_UPDATE;
            case self::ACTION_UPDATE . self::ACTION_PREFIX_ADD_PHOTO:
                return self::HISTORY_MESSAGE_CARRIER_INFO_PHOTO_ADDED;
            case self::ACTION_UPDATE . self::ACTION_PREFIX_DELETE_PHOTO:
                return self::HISTORY_MESSAGE_CARRIER_INFO_PHOTO_DELETED;
            case self::ACTION_UPDATE . self::ACTION_PREFIX_ADD_W9:
                return self::HISTORY_MESSAGE_CARRIER_W9_PHOTO_ADDED;
            case self::ACTION_UPDATE . self::ACTION_PREFIX_DELETE_W9:
                return self::HISTORY_MESSAGE_CARRIER_W9_PHOTO_DELETED;
            case self::ACTION_UPDATE . self::ACTION_PREFIX_ADD_USDOT:
                return self::HISTORY_MESSAGE_CARRIER_USDOT_PHOTO_ADDED;
            case self::ACTION_UPDATE . self::ACTION_PREFIX_DELETE_USDOT:
                return self::HISTORY_MESSAGE_CARRIER_USDOT_PHOTO_DELETED;
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
                $this->company,
                $this->getHistoryMessage(),
                $this->getHistoryMeta(),
            )
        );
    }

    public function update(?string $prefix = null): CarrierEventService
    {
        $this->action = self::ACTION_UPDATE . ($prefix !== null ? $prefix : '');

        $this->setHistory();

        return $this;
    }

    public function broadcast(): CarrierEventService
    {
        if ($this->action === self::ACTION_DELETE) {
            event(new DeleteCarrierBroadcast($this->company->id));
            return $this;
        }
        event(new UpdateCarrierBroadcast($this->company->id));

        return $this;
    }
}
