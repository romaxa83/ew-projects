<?php

namespace App\Services\Vehicles;

use App\Dto\Vehicles\TruckDto;
use App\Enums\Saas\GPS\DeviceHistoryContext;
use App\Exceptions\HasRelatedEntitiesException;
use App\Models\GPS\History;
use App\Models\Saas\GPS\DeviceHistory;
use App\Models\Users\User;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\Vehicle;
use App\Scopes\CompanyScope;
use App\Services\Events\EventService;
use App\Services\Events\Vehicle\VehicleEventService;
use App\Services\Saas\GPS\Histories\HistoryService;
use App\Traits\Services\Vehicles\VehicleHistoryTrait;
use Carbon\CarbonImmutable;
use DB;
use Exception;
use Log;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig;

class TruckService
{
    use VehicleHistoryTrait;

    private ?User $user = null;
    private HistoryService $historyService;

    public function __construct(HistoryService $historyService)
    {
        $this->historyService = $historyService;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function create(TruckDto $dto): Truck
    {
        try {
            DB::beginTransaction();

            $data = $dto->getVehicleData();

            if (!$this->user->isBodyShopUser() && $this->user->getCompany()->isGPSEnabled()) {
                $data['gps_device_id'] = $dto->getGpsDeviceId();
            }

            /** @var Truck $truck */
            $truck = Truck::query()->make($data);

            $truck->saveOrFail();

            $truck->tags()->sync($dto->getTags());

            $this->addAttachments($truck, $dto->getAttachments());

            if ($dto->getRegistrationFile()) {
                $truck->addMediaWithRandomName(
                    Vehicle::REGISTRATION_DOCUMENT_NAME,
                    $dto->getRegistrationFile(),
                    true
                );
            }

            if ($dto->getInspectionFile()) {
                $truck->addMediaWithRandomName(
                    Vehicle::INSPECTION_DOCUMENT_NAME,
                    $dto->getInspectionFile(),
                    true
                );
            }

            EventService::vehicle($truck)
                ->user($this->user)
                ->create();

            DB::commit();

            return $truck;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function update(Truck $truck, TruckDto $dto): Truck
    {
        try {
            DB::beginTransaction();

            $event = EventService::vehicle($truck)
                ->user($this->user);

            $data = $dto->getVehicleData();

            if (!$this->user->isBodyShopUser() && $this->user->getCompany()->isGPSEnabled()) {
                $data['gps_device_id'] = $dto->getGpsDeviceId();

                // если gps истории нет, и устанавливаю gps девайс, создаем запись с историей и присваиваем техники
                if($truck->last_gps_history_id == null && $dto->getGpsDeviceId()){
                    $history = $this->historyService->createEmptyData($truck, $dto->getGpsDeviceId());
                    $data['last_gps_history_id'] = $history->id;
                }
                // если есть последняя запись gps истории, и отвязывают gps девайс, удаляем связь с этой записью
                if($truck->last_gps_history_id != null && $dto->getGpsDeviceId() == null){
                    $data['last_gps_history_id'] = null;
                }
            }

            DeviceHistory::attachOrDetachVehicle($truck, $dto->getGpsDeviceId());

//            logger_info("TRUCK DATA", $data);
            $truck->update($data);

            // добавление в историю gps если сменился водитель
            if($truck->driver_id && $truck->wasChanged('driver_id')){
                $this->historyService->createIfAttachDriver($truck);
            }


            $truck->tags()->sync($dto->getTags());

            $this->addAttachments($truck, $dto->getAttachments());

            if ($dto->getRegistrationFile()) {
                $truck->addMediaWithRandomName(
                    Vehicle::REGISTRATION_DOCUMENT_NAME,
                    $dto->getRegistrationFile(),
                    true
                );
            }

            if ($dto->getInspectionFile()) {
                $truck->addMediaWithRandomName(
                    Vehicle::INSPECTION_DOCUMENT_NAME,
                    $dto->getInspectionFile(),
                    true
                );
            }

            $event->update();

            DB::commit();

            return $truck;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function destroy(Truck $tr): Truck
    {
        if ($tr->hasRelatedOpenOrders() || $tr->hasRelatedDeletedOrders()) {
            throw new HasRelatedEntitiesException();
        }

        if ($tr->hasRelatedClosedOrders()) {
            $tr->delete();
        } else {
            $tr->forceDelete();
        }

        return $tr;
    }

    public function getTrucksWithVin(string $vin, ?int $excludeId, bool $isBodyShop = false)
    {
        $trucks = Truck::select(['id', 'make', 'model', 'unit_number'])
            ->where('vin', $vin);

        if ($isBodyShop) {
            $trucks->withBodyShopCompanies();
        } else {
            $trucks->withGlobalScope('company', new CompanyScope());
        }

        if ($excludeId) {
            $trucks->where('id', '!=', $excludeId);
        }

        return $trucks->get();
    }

    /**
     * @param Truck $truck
     * @param array $attachments
     * @return void
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function addAttachments(Truck $truck, array $attachments): void
    {
        try {
            foreach ($attachments as $attachment) {
                $truck->addMediaWithRandomName(Vehicle::ATTACHMENT_COLLECTION_NAME, $attachment);
            }
        } catch (Exception $e) {
            Log::error($e);
            throw $e;
        }
    }

    public function deleteAttachment(Truck $truck, int $mediaId = 0): void
    {
        if (!$truck->media->find($mediaId)) {
            throw new Exception(trans('File not found.'));
        }

        try {
            DB::beginTransaction();

            $event = EventService::vehicle($truck)
                ->user($this->user);

            $truck->deleteMedia($mediaId);

            $event->update(VehicleEventService::ACTION_VEHICLE_FILE_DELETED);

            DB::commit();

            return;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function deleteDocument(Truck $truck, string $collectionName): void
    {
        try {
            DB::beginTransaction();

            $event = EventService::vehicle($truck)
                ->user($this->user);

            $truck->clearMediaCollection($collectionName);

            $event->update(VehicleEventService::ACTION_VEHICLE_FILE_DELETED);

            DB::commit();

            return;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }

    }
}
