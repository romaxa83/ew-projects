<?php

namespace App\Services\Vehicles;

use App\Dto\Vehicles\TruckDto;
use App\Exceptions\HasRelatedEntitiesException;
use App\Foundations\Modules\History\Services\VehicleHistoryService;
use App\Services\Events\EventService;
use Illuminate\Http\UploadedFile;
use App\Models\Vehicles\Truck;

final class TruckService
{
    public function __construct()
    {}

    public function create(TruckDto $dto): Truck
    {
        return make_transaction(function () use ($dto){
            $model = $this->fill(new Truck(), $dto);

            $model->save();

            $model->tags()->sync($dto->tags);

            if(!empty($dto->files)){
                $this->uploadFiles($model, $dto->files);
            }

            /** @phpstan-ignore-next-line */
            EventService::vehicle($model)
                ->create()
                ->initiator(auth_user())
                ->setHistory()
                ->exec()
            ;

            return $model;
        });
    }

    public function createFromSync(TruckDto $dto): Truck
    {
        $model = $this->fill(new Truck(), $dto);
        $model->company_id = $dto->companyId;
        $model->origin_id = $dto->originId;

        $model->save();

        return $model;
    }

    public function updateFromSync(Truck $model, TruckDto $dto): Truck
    {
        $model = $this->fill($model, $dto);
        $model->deleted_at = null;

        $model->save();

        return $model;
    }

    public function update(Truck $model, TruckDto $dto): Truck
    {
        return make_transaction(function () use ($model, $dto){

            $old = $model->dataForUpdateHistory();

            /** @phpstan-ignore-next-line */
            $event = EventService::vehicle($model)
                ->initiator(auth_user());

            $model = $this->fill($model, $dto);

            $model->update();

            $model->tags()->sync($dto->tags);

            if(!empty($dto->files)){
                $this->uploadFiles($model, $dto->files);
            }

            $event->update()
                ->setHistory($old)
                ->exec()
            ;

            return $model;
        });
    }

    private function fill(Truck $model, TruckDto $dto): Truck
    {
        $model->vin = $dto->vin;
        $model->unit_number = $dto->unitNumber;
        $model->make = $dto->make;
        $model->model = $dto->model;
        $model->year = $dto->year;
        $model->type = $dto->type;
        $model->license_plate = $dto->licensePlate;
        $model->notes = $dto->notes;
        $model->color = $dto->color;
        $model->gvwr = $dto->gvwr;
        $model->customer_id = $dto->customerId;
        $model->temporary_plate = $dto->temporaryPlate;

        return $model;
    }

    public function delete(Truck $model): bool
    {
        if ($model->hasRelatedOpenOrders() || $model->hasRelatedDeletedOrders()) {
            throw new HasRelatedEntitiesException();
        }

        if ($model->hasRelatedClosedOrders()) {
            return  $model->delete();
        }

        return $model->forceDelete();
    }

    public function uploadFile(Truck $model, UploadedFile $file): Truck
    {
        $model->addMediaWithRandomName(Truck::ATTACHMENT_COLLECTION_NAME, $file);

        return $model;
    }

    public function uploadFiles(Truck $model, array $attachments = []): void
    {
        foreach ($attachments as $attachment) {
            $this->uploadFile($model, $attachment);
        }
    }

    public function deleteFile(Truck $model, int $mediaId = 0): void
    {
        if ($media = $model->media->find($mediaId)) {
            $clone = clone $media;

            $model->deleteMedia($mediaId);

            /** @phpstan-ignore-next-line */
            EventService::vehicle($model)
                ->custom(VehicleHistoryService::ACTION_FILE_DELETED)
                ->initiator(auth_user())
                ->setMedia($clone)
                ->setHistory()
                ->exec()
            ;

            return;
        }

        throw new \Exception(__('exceptions.file.not_found'));
    }
}
