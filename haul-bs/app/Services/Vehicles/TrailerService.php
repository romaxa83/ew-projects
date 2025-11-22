<?php

namespace App\Services\Vehicles;

use App\Dto\Vehicles\TrailerDto;
use App\Exceptions\HasRelatedEntitiesException;
use App\Foundations\Modules\History\Services\VehicleHistoryService;
use App\Models\Vehicles\Trailer;
use App\Services\Events\EventService;
use Illuminate\Http\UploadedFile;

final class TrailerService
{
    public function __construct()
    {}

    public function create(TrailerDto $dto): Trailer
    {
        return make_transaction(function () use ($dto){
            $model = $this->fill(new Trailer(), $dto);

            $model->save();

            $model->tags()->sync($dto->tags);

            if(!empty($dto->files)){
                $this->uploadFiles($model, $dto->files);
            }

            EventService::vehicle($model)
                ->create()
                ->initiator(auth_user())
                ->setHistory()
                ->exec()
            ;

            return $model;
        });
    }

    public function createFromSync(TrailerDto $dto): Trailer
    {
        $model = $this->fill(new Trailer(), $dto);
        $model->company_id = $dto->companyId;
        $model->origin_id = $dto->originId;

        $model->save();

        return $model;
    }

    public function updateFromSync(Trailer $model, TrailerDto $dto): Trailer
    {
        $model = $this->fill($model, $dto);
        $model->deleted_at = null;

        $model->save();

        return $model;
    }

    public function update(Trailer $model, TrailerDto $dto): Trailer
    {
        return make_transaction(function () use ($model, $dto){
            $old = $model->dataForUpdateHistory();

            $event = EventService::vehicle($model)
                ->initiator(auth_user());

            $model = $this->fill($model, $dto);

            $model->save();

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

    private function fill(Trailer $model, TrailerDto $dto): Trailer
    {
        $model->vin = $dto->vin;
        $model->unit_number = $dto->unitNumber;
        $model->make = $dto->make;
        $model->model = $dto->model;
        $model->year = $dto->year;
        $model->type = $dto->type;
        $model->license_plate = $dto->licensePlate;
        $model->temporary_plate = $dto->temporaryPlate;
        $model->notes = $dto->notes;
        $model->color = $dto->color;
        $model->gvwr = $dto->gvwr;
        $model->customer_id = $dto->customerId;

        return $model;
    }

    public function delete(Trailer $model): bool
    {
        if ($model->hasRelatedOpenOrders() || $model->hasRelatedDeletedOrders()) {
            throw new HasRelatedEntitiesException();
        }

        if ($model->hasRelatedClosedOrders()) {
            return $model->delete();
        }

        return $model->forceDelete();
    }

    public function uploadFile(Trailer $model, UploadedFile $file): Trailer
    {
        $model->addMediaWithRandomName(Trailer::ATTACHMENT_COLLECTION_NAME, $file);

        return $model;
    }

    public function uploadFiles(Trailer $model, array $attachments = []): void
    {
        foreach ($attachments as $attachment) {
            $this->uploadFile($model, $attachment);
        }
    }

    public function deleteFile(Trailer $model, int $mediaId = 0): void
    {
        if ($media = $model->media->find($mediaId)) {
            $clone = clone $media;

            $model->deleteMedia($mediaId);

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

