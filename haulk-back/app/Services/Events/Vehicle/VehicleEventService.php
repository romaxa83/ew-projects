<?php

namespace App\Services\Events\Vehicle;

use App\Events\ModelChanged;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Vehicle;
use App\Services\Events\EventService;
use App\Services\Histories\VehicleHistoryHandler;
use TypeError;

class VehicleEventService extends EventService
{
    public const ACTION_VEHICLE_FILE_DELETED = 'vehicle_file_deleted';
    public const ACTION_VEHICLE_COMMENT_CREATED = 'vehicle_comment_created';
    public const ACTION_VEHICLE_COMMENT_DELETED = 'vehicle_comment_deleted';
    protected const HISTORY_MESSAGE_VEHICLE_CREATED = 'history.vehicle_created';
    protected const HISTORY_MESSAGE_VEHICLE_CHANGED = 'history.vehicle_updated';
    protected const HISTORY_MESSAGE_VEHICLE_FILE_DELETED = 'history.vehicle_file_deleted';
    protected const HISTORY_MESSAGE_VEHICLE_COMMENT_CREATED = 'history.vehicle_comment_created';
    protected const HISTORY_MESSAGE_VEHICLE_COMMENT_DELETED = 'history.vehicle_comment_deleted';
    private Vehicle $vehicle;

    private Vehicle $vehicleForHandler;

    private ?VehicleHistoryHandler $historyHandler = null;

    public function __construct(?Vehicle $vehicle = null)
    {
        if ($vehicle === null) {
            return;
        }

        $this->vehicle = $vehicle;
        $this->vehicleForHandler = clone $vehicle;

        try {
            $this->historyHandler = (new VehicleHistoryHandler())->setOrigin($this->vehicleForHandler);
        } catch (TypeError $e) {
            $this->historyHandler = null;
        }
    }

    private function refreshObject(): void
    {
        $this->vehicle->refresh();

        if ($this->historyHandler === null) {
            return;
        }

        $this->historyHandler->setDirty($this->vehicle);
    }

    private function getHistoryMessage(): ?string
    {
        switch ($this->action) {
            case self::ACTION_CREATE:
                return  self::HISTORY_MESSAGE_VEHICLE_CREATED;
            case self::ACTION_UPDATE:
                return self::HISTORY_MESSAGE_VEHICLE_CHANGED;
            case self::ACTION_VEHICLE_FILE_DELETED:
                return self::HISTORY_MESSAGE_VEHICLE_FILE_DELETED;
            case self::ACTION_VEHICLE_COMMENT_CREATED:
                return self::HISTORY_MESSAGE_VEHICLE_COMMENT_CREATED;
            case self::ACTION_VEHICLE_COMMENT_DELETED:
                return self::HISTORY_MESSAGE_VEHICLE_COMMENT_DELETED;

        }

        return null;
    }

    private function getHistoryMeta(): array
    {
        $truckTypeName = $this->vehicle->isBodyShopVehicle() ? 'vehicle' : 'truck';
        $vehicleTypeName = get_class($this->vehicle) === Trailer::class
            ? 'trailer'
            : $truckTypeName;

        return [
            'role' => $this->vehicle->isBodyShopVehicle() ? trans('history.bs.' . $this->user->getRoleName()) : $this->user->getRoleName(),
            'full_name' => $this->user->full_name,
            'email' => $this->user->email,
            'user_id' => $this->user->id,
            'vehicle_type' => trans('history.vehicle_types.' . $vehicleTypeName),
        ];
    }

    private function setHistory(
        ?array $meta = null,
        ?string $message = null,
        ?int $performed_at = null,
        bool $force = false
    ): void
    {
        $message = $message ?? $this->getHistoryMessage();

        if(!$force){
            if (!$this->isVehicleUpdated($message)) {
                return;
            }
        }

        $meta = $meta ?? $this->getHistoryMeta();

        event(
            new ModelChanged(
                $this->vehicle,
                $message,
                $meta,
                $performed_at,
                null,
                $this->historyHandler
            )
        );
    }

    private function isVehicleUpdated(string $message): bool
    {
        if ($message === self::HISTORY_MESSAGE_VEHICLE_CREATED) {
            return true;
        }

        if ($this->historyHandler === null) {
            return false;
        }

        $comparisons = $this->historyHandler->start();

        if (empty($comparisons)) {
            return false;
        }

        return true;
    }

    public function create(): VehicleEventService
    {
        $this->action = self::ACTION_CREATE;

        if ($this->historyHandler) {
            $this->historyHandler->setOrigin(null);
            $this->historyHandler->setDirty($this->vehicleForHandler);
        }

        $this->setHistory();

        $this->setDriverHistory(true);
        $this->setOwnerHistory(true);

        return $this;
    }

    public function update(?string $action = null, bool $force = false): VehicleEventService
    {
        $this->action = $action ?? self::ACTION_UPDATE;

        if ($this->historyHandler) {
            $this->historyHandler->setOrigin($this->vehicleForHandler);
        }

        $this->refreshObject();

        $this->setHistory(null, null, null, $force);

        $this->setDriverHistory();
        $this->setOwnerHistory();

        return $this;
    }

    private function setDriverHistory(bool $isCreated = false): void
    {
        if ($isCreated || $this->vehicle->driver_id !== $this->vehicleForHandler->driver_id) {

            if (!$isCreated && $this->vehicleForHandler->driver_id) {
                $previousDriver = $this->vehicle->driversHistory()->where([
                    'driver_id' => $this->vehicleForHandler->driver_id,
                    'unassigned_at' => null,
                ])->latest('assigned_at')->first();

                if ($previousDriver) {
                    $previousDriver->unassigned_at = now();
                    $previousDriver->save();
                }
            }

            if ($this->vehicle->driver_id ?? null) {
                $this->vehicle->driversHistory()->create([
                    'driver_id' => $this->vehicle->driver_id,
                    'assigned_at' => now(),
                ]);
            }
        }
    }

    private function setOwnerHistory(bool $isCreated = false): void
    {
        if ($isCreated || $this->vehicle->owner_id !== $this->vehicleForHandler->owner_id) {

            if (!$isCreated && $this->vehicleForHandler->owner_id) {
                $previousOwner = $this->vehicle->ownersHistory()->where([
                    'owner_id' => $this->vehicleForHandler->owner_id,
                    'unassigned_at' => null,
                ])->latest('assigned_at')->first();

                if ($previousOwner) {
                    $previousOwner->unassigned_at = now();
                    $previousOwner->save();
                }
            }

            if ($this->vehicle->owner_id ?? null) {
                $this->vehicle->ownersHistory()->create([
                    'owner_id' => $this->vehicle->owner_id,
                    'assigned_at' => now(),
                ]);
            }
        }
    }
}
