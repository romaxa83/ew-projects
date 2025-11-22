<?php


namespace App\Services\Inspections;


use App\Contracts\Models\HasGuard;
use App\Dto\Inspections\InspectionDto;
use App\Dto\Inspections\InspectionPhotosDto;
use App\Enums\Inspections\InspectionModerationEntityEnum;
use App\Enums\Inspections\InspectionModerationFieldEnum;
use App\Enums\Permissions\GuardsEnum;
use App\Enums\Vehicles\VehicleFormEnum;
use App\Exceptions\Inspections\InspectionCanNotUpdateException;
use App\Exceptions\Inspections\InspectionValidationException;
use App\Models\Admins\Admin;
use App\Models\Inspections\Inspection;
use App\Models\Inspections\InspectionTire;
use App\Models\Tires\Tire;
use App\Models\Users\User;
use App\Models\Vehicles\Vehicle;
use App\Services\Vehicles\VehicleService;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class InspectionService
{
    public function __construct(protected InspectionTireService $inspectionTireService)
    {}

    public function create(InspectionDto $dto, User $inspector): Inspection
    {
        return $this->editInspection(
            $dto,
            new Inspection(['inspector_id' => $inspector->id])
        );
    }

    public function update(InspectionDto $dto, Inspection $inspection, HasGuard $guard): Inspection
    {
        if ($guard->getGuard() === GuardsEnum::USER && ($dto->getTime()
                    ->getTimestamp() - $inspection->created_at->getTimestamp()) > Inspection::UPDATED_TIME) {
            throw new InspectionCanNotUpdateException();
        }

        return $this->editInspection($dto, $inspection);
    }

    private function editInspection(InspectionDto $dto, Inspection $inspection): Inspection
    {
        $vehicle = Vehicle::find($dto->getVehicleId());

        $inspector = User::find($inspection->inspector_id);

        $inspection->branch_id = $inspector->branch?->id;
        $inspection->vehicle_id = $dto->getVehicleId();
        $inspection->driver_id = $dto->getDriverId();
        $inspection->inspection_reason_id = $dto->getInspectionReasonId();
        $inspection->inspection_reason_description = $dto->getInspectionReasonDescription();
        $inspection->unable_to_sign = $dto->isUnableToSign();
        $inspection->odo = $dto->getOdo();
        $inspection->created_at = $inspection->created_at ?? $dto->getTime()
            ->toDateTimeString();
        $inspection->updated_at = $dto->getTime()
            ->toDateTimeString();
        $inspection->moderation_fields = $this->checkInspectionField($dto, $inspection, $vehicle);
        $inspection->save();

        $vehicle->odo = $inspection->odo;
        $vehicle->save();

        try {
            $this->saveInspectionPhotos($dto->getPhotos(), $inspection);
        } catch (Exception $exception) {
            Log::error($exception);
        }

        $inspection = $this->saveInspectionTires($dto, $inspection);

        $inspection->is_moderated = !$inspection->shouldModerated();

        if ($inspection->isDirty()) {
            $inspection->save();
        }

        logger('INSPECTION CREATE END', ['vehicle_moderation' => $vehicle->isModerated()]);

        return $inspection;
    }

    private function checkInspectionField(InspectionDto $dto, Inspection $inspection, Vehicle $vehicle): array
    {
        $moderationFields = [];

        /**
         * If on MAIN vehicle set too small odometer reading
         */
        if ($dto->getOdo() !== null && $vehicle->form->is(VehicleFormEnum::MAIN)) {
            $lastOdo = $vehicle->inspections()
                ->where('id', '<>', $inspection->id)
                ->first()?->odo;
            /**
             * If odo in last inspection is bigger than odo in current inspection
             */
            logger('MODERATION CHECK - 1');
            if ($lastOdo > $dto->getOdo() || ($lastOdo === null && $vehicle->odo > $dto->getOdo())) {
                $moderationFields[] = [
                    'entity' => InspectionModerationEntityEnum::VEHICLE,
                    'field' => InspectionModerationFieldEnum::ODO,
                    'message' => 'inspections.validation_messages.odo.too_small'
                ];
            }
        }

        /**
         * If on MAIN vehicle is not set odometer reading
         */
        if ($dto->getOdo() === null && $vehicle->form->is(VehicleFormEnum::MAIN)) {
            if ($dto->isNotOffline()) {
                throw new InspectionValidationException('inspections.validation_messages.odo.is_required');
            }
            logger('MODERATION CHECK - 2');
            $moderationFields[] = [
                'entity' => InspectionModerationEntityEnum::VEHICLE,
                'field' => InspectionModerationFieldEnum::ODO,
                'message' => 'inspections.validation_messages.odo.is_required'
            ];
        }

        /**
         * If not check "unable_to_sign" and sign does not exists
         */
        if ($dto->isNotUnableToSign() && !$dto->getPhotos()?->getSign() && !$inspection->getFirstMedia(
                Inspection::MC_SIGN
            )?->exists()) {
            if ($dto->isNotOffline()) {
                throw new InspectionValidationException('inspections.validation_messages.photos.sign.is_required');
            }
            logger('MODERATION CHECK - 3');
            $moderationFields[] = [
                'entity' => InspectionModerationEntityEnum::VEHICLE,
                'field' => InspectionModerationFieldEnum::PHOTO_SIGN,
                'message' => 'inspections.validation_messages.photos.sign.is_required'
            ];
        }

        /**
         * If it is new inspection and not upload vehicle photo
         */
        $vehiclePhotoExists = $dto->getPhotos()?->getVehicle()
            || $inspection->getFirstMedia(Inspection::MC_VEHICLE)?->exists()
            || $inspection->vehicle?->lastInspection()?->getFirstMedia(Inspection::MC_VEHICLE)?->exists();
        if (!$vehiclePhotoExists) {
            if ($dto->isNotOffline()) {
                throw new InspectionValidationException('inspections.validation_messages.photos.vehicle.is_required');
            }
            logger('MODERATION CHECK - 4');
            $moderationFields[] = [
                'entity' => InspectionModerationEntityEnum::VEHICLE,
                'field' => InspectionModerationFieldEnum::PHOTO_VEHICLE,
                'message' => 'inspections.validation_messages.photos.vehicle.is_required'
            ];
        }

        /**
         * If it is new inspection and not upload state number photo
         */
        if (!$dto->getPhotos()?->getStateNumber() && !$inspection->getFirstMedia(Inspection::MC_STATE_NUMBER)?->exists(
            )) {
            if ($dto->isNotOffline()) {
                throw new InspectionValidationException(
                    'inspections.validation_messages.photos.state_number.is_required'
                );
            }
            logger('MODERATION CHECK - 5');
            $moderationFields[] = [
                'entity' => InspectionModerationEntityEnum::VEHICLE,
                'field' => InspectionModerationFieldEnum::PHOTO_STATE_NUMBER,
                'message' => 'inspections.validation_messages.photos.state_number.is_required'
            ];
        }

        /**
         * If it is new inspection and not upload odometer photo and vehicle type is MAIN
         */
        if (!$dto->getPhotos()?->getOdo() && $vehicle->form->is(VehicleFormEnum::MAIN) && !$inspection->getFirstMedia(
                Inspection::MC_ODO
            )?->exists()) {
            if ($dto->isNotOffline()) {
                throw new InspectionValidationException('inspections.validation_messages.photos.odo.is_required');
            }
            logger('MODERATION CHECK - 6');
            $moderationFields[] = [
                'entity' => InspectionModerationEntityEnum::VEHICLE,
                'field' => InspectionModerationFieldEnum::PHOTO_ODO,
                'message' => 'inspections.validation_messages.photos.odo.is_required'
            ];
        }
        logger('MODERATION DATA', $moderationFields);
        return $moderationFields;
    }

    /**
     * @param InspectionPhotosDto|null $dto
     * @param Inspection $inspection
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    private function saveInspectionPhotos(
        ?InspectionPhotosDto $dto,
        Inspection $inspection
    ): void {
        /** @var $vehicleService  VehicleService */
        $vehicleService = resolve(VehicleService::class);

        $previousInspection = $inspection->previousVehicleInspection();

        if ($dto?->getStateNumber()) {
            $inspection
                ->clearMediaCollection(Inspection::MC_STATE_NUMBER)
                ->copyMedia($dto->getStateNumber())
                ->toMediaCollection(Inspection::MC_STATE_NUMBER);

            $vehicleService->saveVehicleStateNumberPhoto($dto->getStateNumber(), $inspection->vehicle);
        }

        $vehiclePhoto = $dto?->getVehicle()
            ?: $previousInspection?->getFirstMediaPath(Inspection::MC_VEHICLE);

        if ($vehiclePhoto) {
            if(
                $vehiclePhoto instanceof UploadedFile
                || (
                    is_string($vehiclePhoto)
                    && file_exists($vehiclePhoto)
                    && ($inspection->getFirstMedia(Inspection::MC_VEHICLE) === null)
                )
            ){
                $inspection
                    ->clearMediaCollection(Inspection::MC_VEHICLE)
                    ->copyMedia($vehiclePhoto)
                    ->toMediaCollection(Inspection::MC_VEHICLE);
            }
            if (!is_string($vehiclePhoto)) {
                $vehicleService->saveVehiclePhoto($vehiclePhoto, $inspection->vehicle);
            }
        }

        $dataSheet1Photo = $dto?->getDataSheet1()
            ?: $previousInspection?->getFirstMediaPath(Inspection::MC_DATA_SHEET_1);

        if ($dataSheet1Photo) {
            if(
                $dataSheet1Photo instanceof UploadedFile
                || (
                    is_string($dataSheet1Photo)
                    && file_exists($dataSheet1Photo)
                    && $inspection->getFirstMedia(Inspection::MC_DATA_SHEET_1) === null
                )
            ){
                $inspection
                    ->clearMediaCollection(Inspection::MC_DATA_SHEET_1)
                    ->copyMedia($dataSheet1Photo)
                    ->toMediaCollection(Inspection::MC_DATA_SHEET_1);
            }
        }

        $dataSheet2Photo = $dto?->getDataSheet2()
            ?: $previousInspection?->getFirstMediaPath(Inspection::MC_DATA_SHEET_2);
        if ($dataSheet2Photo) {
            if(
                $dataSheet2Photo instanceof UploadedFile
                || (
                    is_string($dataSheet2Photo)
                    && file_exists($dataSheet2Photo)
                    && $inspection->getFirstMedia(Inspection::MC_DATA_SHEET_2) === null
                )
            ){
                $inspection
                    ->clearMediaCollection(Inspection::MC_DATA_SHEET_2)
                    ->copyMedia($dataSheet2Photo)
                    ->toMediaCollection(Inspection::MC_DATA_SHEET_2);
            }
        }

        if ($dto?->getOdo()) {
            $inspection
                ->clearMediaCollection(Inspection::MC_ODO)
                ->addMedia($dto->getOdo())
                ->toMediaCollection(Inspection::MC_ODO);
        }

        if ($dto?->getSign()) {
            $inspection
                ->clearMediaCollection(Inspection::MC_SIGN)
                ->addMedia($dto->getSign())
                ->toMediaCollection(Inspection::MC_SIGN);
        }

        if ($inspection->unable_to_sign) {
            $inspection
                ->clearMediaCollection(Inspection::MC_SIGN);
        }
    }

    private function saveInspectionTires(InspectionDto $dto, Inspection $inspection): Inspection
    {
        $moderationFields = $inspection->moderation_fields;

        foreach ($dto->getTires() as $tireDto) {
            /**@var InspectionTire $tireInspection */
            $tireInspection = $inspection
                ->inspectionTires()
                ->updateOrCreate(
                    [
                        'schema_wheel_id' => $tireDto->getSchemaWheelId(),
                    ],
                    [
                        'tire_id' => $tireDto->getTireId(),
                        'ogp' => $tireDto->getOgp(),
                        'pressure' => $tireDto->getPressure(),
                        'comment' => $tireDto->getComment(),
                        'no_problems' => $tireDto->isNoProblems()
                    ]
                );

            $tire = Tire::with(['tireInspections', 'specification'])
                ->find($tireDto->getTireId());

            if ($tireDto->getOgp() > $tire->specification->ngp) {
                if ($dto->isNotOffline()) {
                    throw new InspectionValidationException('inspections.validation_messages.tire.ogp_bigger_ngp');
                }

                $moderationFields[] = [
                    'entity' => InspectionModerationEntityEnum::TIRE,
                    'id' => $tireInspection->id,
                    'field' => InspectionModerationFieldEnum::OGP,
                    'message' => 'inspections.validation_messages.tire.ogp_bigger_ngp'
                ];
            } else {
                /**@var InspectionTire $lastTireInspection */
                $lastTireInspection = $tire->tireInspections->first(
                    fn(InspectionTire $item) => $item->inspection_id < $inspection->id
                );

                if ($lastTireInspection && $lastTireInspection->ogp < $tireInspection->ogp) {
                    $moderationFields[] = [
                        'entity' => InspectionModerationEntityEnum::TIRE,
                        'id' => $tireInspection->id,
                        'field' => InspectionModerationFieldEnum::OGP,
                        'message' => 'inspections.validation_messages.tire.ogp_too_big'
                    ];
                }
                $tire->ogp = $tireDto->getOgp();
                $tire->save();
            }

            if ($tireDto->isNoProblems()) {
                $tireInspection
                    ->problems()
                    ->detach();
            } else {
                $tireInspection
                    ->problems()
                    ->sync($tireDto->getProblems());
            }

            if (!$tireDto->getRecommendations()) {
                $tireInspection
                    ->recommendations()
                    ->detach();
            } else {
                $sync = [];
                foreach ($tireDto->getRecommendations() as $recommendationDto) {
                    $sync[$recommendationDto->getRecommendationId()] = [
                        'is_confirmed' => $recommendationDto->isConfirmed(),
                        'new_tire_id' => $recommendationDto->getNewTireId()
                    ];
                }
                $tireInspection
                    ->recommendations()
                    ->sync($sync);
            }

            $this->inspectionTireService->uploadPhotoFromBase64($tireInspection, $tireDto->photos);
        }
        $inspection->moderation_fields = $moderationFields;

        if ($inspection->isDirty('moderation_fields')) {
            $inspection->is_moderated = false;
            $inspection->save();
        }

        return $inspection;
    }

    public function linked(Inspection $mainInspection, Inspection $trailerInspection, HasGuard $guard): Inspection
    {
        $this->checkUpdatingTimeLimits($mainInspection, $trailerInspection, $guard);

        $trailerInspection->main_id = $mainInspection->id;
        $trailerInspection->save();

        return $mainInspection->refresh();
    }

    public function unlinked(Inspection $inspection, HasGuard $user): Collection
    {
        $mainInspection = $inspection->vehicle->form->isNot(VehicleFormEnum::MAIN)
            ? $inspection->main
            : $inspection;
        $this->checkUpdatingTimeLimits($mainInspection, $mainInspection->trailer, $user);

        $mainInspection->trailer->main_id = null;
        $mainInspection->trailer->save();

        $inspections = Inspection::whereIn('id', [$mainInspection->trailer->id, $mainInspection->id]);
        if ($user->getGuard() === GuardsEnum::USER) {
            $inspections->my($user);
        }

        return $inspections->get();
    }

    public function list(array $args, array $select, array $relations, User|Admin $user): LengthAwarePaginator
    {
        $query = Inspection::filter($args);

        if (!empty($args['only_mine'])) {
            $query->my($user);
        }

        return $query->select($select)
            ->with($relations)
            ->orderBy('created_at')
            ->paginate(
                perPage: $args['per_page'],
                page: $args['page']
            );
    }

    public function updateInspectionTireOgp(InspectionTire $inspectionTire, float $ogp): InspectionTire
    {
        $inspectionTire->ogp = $ogp;
        $inspectionTire->save();

        $tire = $inspectionTire->tire;

        if ($ogp > $tire->specification->ngp) {
            throw new InspectionValidationException('inspections.validation_messages.tire.ogp_bigger_ngp');
        }

        $inspection = $inspectionTire->inspection;

        /**@var InspectionTire $lastTireInspection */
        $lastTireInspection = $tire->tireInspections->first(
            fn(InspectionTire $item) => $item->inspection_id !== $inspection->id
        );

        $moderationFields = $inspection->moderation_fields;
        foreach ($moderationFields as $key => $value) {
            if (
                $value['entity'] === InspectionModerationEntityEnum::TIRE
                && $value['id'] === $inspectionTire->id
                && $value['field'] === InspectionModerationFieldEnum::OGP
            ) {
                unset($moderationFields[$key]);
            }
        }

        if ($lastTireInspection && $lastTireInspection->ogp < $inspectionTire->ogp) {
            $moderationFields[] = [
                'entity' => InspectionModerationEntityEnum::TIRE,
                'id' => $inspectionTire->id,
                'field' => InspectionModerationFieldEnum::OGP,
                'message' => 'inspections.validation_messages.tire.ogp_too_big'
            ];
        }

        $inspection->moderation_fields = array_values($moderationFields);
        $inspection->save();

        $tire->ogp = $ogp;
        $tire->save();

        return $inspectionTire;
    }

    private function checkUpdatingTimeLimits(Inspection $inspection1, Inspection $inspection2, HasGuard $guard)
    {
        if ($guard->getGuard() === GuardsEnum::ADMIN) {
            return;
        }

        $currentTime = now();
        if (
            ($currentTime->getTimestamp() - $inspection1->created_at->getTimestamp()) > Inspection::UPDATED_TIME
            || ($currentTime->getTimestamp() - $inspection2->created_at->getTimestamp()) > Inspection::UPDATED_TIME
        ) {
            throw new InspectionCanNotUpdateException();
        }
    }
}
