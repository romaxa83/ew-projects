<?php

namespace App\Services\Vehicles;

use App\Dto\Vehicles\TrailerDto;
use App\Exceptions\HasRelatedEntitiesException;
use App\Models\Saas\GPS\DeviceHistory;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Vehicle;
use App\Scopes\CompanyScope;
use App\Services\Events\EventService;
use App\Services\Events\Vehicle\VehicleEventService;
use App\Services\Saas\GPS\Histories\HistoryService;
use App\Traits\Services\Vehicles\VehicleHistoryTrait;
use DB;
use Exception;
use Log;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig;

class TrailerService
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

    public function create(TrailerDto $dto): Trailer
    {
        try {
            DB::beginTransaction();

            $data = $dto->getVehicleData();

            if (!$this->user->isBodyShopUser() && $this->user->getCompany()->isGPSEnabled()) {
                $data['gps_device_id'] = $dto->getGpsDeviceId();
            }

            /** @var Trailer $trailer */
            $trailer = Trailer::query()->make($data);

            $trailer->saveOrFail();

            $trailer->tags()->sync($dto->getTags());

            $this->addAttachments($trailer, $dto->getAttachments());

            if ($dto->getRegistrationFile()) {
                $trailer->addMediaWithRandomName(
                    Vehicle::REGISTRATION_DOCUMENT_NAME,
                    $dto->getRegistrationFile(),
                    true
                );
            }

            if ($dto->getInspectionFile()) {
                $trailer->addMediaWithRandomName(
                    Vehicle::INSPECTION_DOCUMENT_NAME,
                    $dto->getInspectionFile(),
                    true
                );
            }

            EventService::vehicle($trailer)
                ->user($this->user)
                ->create();

            DB::commit();

            return $trailer;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function update(Trailer $trailer, TrailerDto $dto): Trailer
    {
        try {
            DB::beginTransaction();

            $event = EventService::vehicle($trailer)
                ->user($this->user);

            $data = $dto->getVehicleData();

            if (!$this->user->isBodyShopUser() && $this->user->getCompany()->isGPSEnabled()) {
                $data['gps_device_id'] = $dto->getGpsDeviceId();

                // если gps истории нет, и устанавливаю gps девайс, создаем запись с историей и присваиваем техники
                if($trailer->last_gps_history_id == null && $dto->getGpsDeviceId()){
                    $history = $this->historyService->createEmptyData($trailer, $dto->getGpsDeviceId());
                    $data['last_gps_history_id'] = $history->id;
                }

                // если есть последняя запись gps истории, и отвязывают gps девайс, удаляем связь с этой записью
                if($trailer->last_gps_history_id != null && $dto->getGpsDeviceId() == null){
                    $data['last_gps_history_id'] = null;
                }
            }

            DeviceHistory::attachOrDetachVehicle($trailer, $dto->getGpsDeviceId());

            $trailer->update($data);

            // добавление в историю gps если сменился водитель
            if($trailer->driver_id && $trailer->wasChanged('driver_id')){
                $this->historyService->createIfAttachDriver($trailer);
            }

            $trailer->tags()->sync($dto->getTags());

            $this->addAttachments($trailer, $dto->getAttachments());

            if ($dto->getRegistrationFile()) {
                $trailer->addMediaWithRandomName(
                    Vehicle::REGISTRATION_DOCUMENT_NAME,
                    $dto->getRegistrationFile(),
                    true
                );
            }

            if ($dto->getInspectionFile()) {
                $trailer->addMediaWithRandomName(
                    Vehicle::INSPECTION_DOCUMENT_NAME,
                    $dto->getInspectionFile(),
                    true
                );
            }

            $event->update();

            DB::commit();

            return $trailer;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function destroy(Trailer $tr): Trailer
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

    public function getTrailersWithVin(string $vin, ?int $excludeId, bool $isBodyShop = false)
    {
        $trailers = Trailer::select(['id', 'make', 'model', 'unit_number'])
            ->where('vin', $vin);

        if ($isBodyShop) {
            $trailers->withBodyShopCompanies();
        } else {
            $trailers->withGlobalScope('company', new CompanyScope());
        }

        if ($excludeId) {
            $trailers->where('id', '!=', $excludeId);
        }

        return $trailers->get();
    }


    /**
     * @param Trailer $trailer
     * @param array $attachments
     * @return void
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function addAttachments(Trailer $trailer, array $attachments): void
    {
        try {
            foreach ($attachments as $attachment) {
                $trailer->addMediaWithRandomName(Vehicle::ATTACHMENT_COLLECTION_NAME, $attachment);
            }
        } catch (Exception $e) {
            Log::error($e);
            throw $e;
        }
    }

    public function deleteAttachment(Trailer $trailer, int $mediaId = 0): void
    {
        if (!$trailer->media->find($mediaId)) {
            throw new Exception(trans('File not found.'));
        }

        try {
            DB::beginTransaction();

            $event = EventService::vehicle($trailer)
                ->user($this->user);

            $trailer->deleteMedia($mediaId);

            $event->update(VehicleEventService::ACTION_VEHICLE_FILE_DELETED);

            DB::commit();

            return;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function deleteDocument(Trailer $trailer, string $collectionName): void
    {
        $trailer->clearMediaCollection($collectionName);
    }
}
