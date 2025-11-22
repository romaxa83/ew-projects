<?php

namespace App\Services\BodyShop\VehicleOwners;

use App\Dto\VehicleOwners\VehicleOwnerDto;
use App\Exceptions\HasRelatedEntitiesException;
use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use DB;
use Exception;
use Illuminate\Http\UploadedFile;
use Log;

class VehicleOwnerService
{
    public function create(VehicleOwnerDto $dto): VehicleOwner
    {
        try {
            DB::beginTransaction();

            /** @var VehicleOwner $vehicleOwner */
            $vehicleOwner = VehicleOwner::query()->make($dto->getVehicleOwnerData());

            $vehicleOwner->saveOrFail();

            $this->addAttachments($vehicleOwner, $dto->getAttachments());

            $vehicleOwner->tags()->sync($dto->getTags());

            DB::commit();

            return $vehicleOwner;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function update(VehicleOwner $vehicleOwner, VehicleOwnerDto $dto): VehicleOwner
    {
        try {
            DB::beginTransaction();

            $vehicleOwner->update($dto->getVehicleOwnerData());

            $this->addAttachments($vehicleOwner, $dto->getAttachments());

            $vehicleOwner->tags()->sync($dto->getTags());

            DB::commit();

            return $vehicleOwner;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function destroy(VehicleOwner $vehicleOwner): VehicleOwner
    {
        if ($vehicleOwner->trucks()->exists() || $vehicleOwner->trailers()->exists()) {
            throw new HasRelatedEntitiesException();
        }

        $vehicleOwner->delete();

        return $vehicleOwner;
    }

    public function addAttachment(VehicleOwner $vehicleOwner, UploadedFile $file): VehicleOwner
    {
        try {
            $vehicleOwner->addMediaWithRandomName(VehicleOwner::ATTACHMENT_COLLECTION_NAME, $file);

            return $vehicleOwner;
        } catch (Exception $e) {
            Log::error($e);
            throw $e;
        }
    }

    public function addAttachments(VehicleOwner $vehicleOwner, array $attachments = []): void
    {
        foreach ($attachments as $attachment) {
            $this->addAttachment($vehicleOwner, $attachment);
        }
    }

    public function deleteAttachment(VehicleOwner $vehicleOwner, int $mediaId = 0): void
    {
        if ($vehicleOwner->media->find($mediaId)) {

            $vehicleOwner->deleteMedia($mediaId);

            return;
        }

        throw new Exception(trans('File not found.'));
    }
}
