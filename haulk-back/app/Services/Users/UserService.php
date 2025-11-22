<?php

namespace App\Services\Users;

use App\Dto\DriverDto;
use App\Dto\UserDto;
use App\Http\Requests\Users\UserHistoryRequest;
use App\Models\History\History;
use App\Models\Orders\Order;
use App\Models\Users\DriverInfo;
use App\Models\Users\DriverLicense;
use App\Models\Users\User;
use App\Services\Events\EventService;
use App\Services\Events\User\UserEventService;
use App\Services\Orders\OrderSearchService;
use Auth;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Log;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig;
use Spatie\MediaLibrary\Exceptions\MediaCannotBeDeleted;
use Throwable;

class UserService
{

    private UserNotificationService $userNotificationService;

    private ?User $loggedUser;

    public function __construct(UserNotificationService $userNotificationService)
    {
        $this->userNotificationService = $userNotificationService;
    }

    public function setLoggedUser(?User $user): self
    {
        $this->loggedUser = $user;

        return $this;
    }

    /**
     * @param UserDto $dto
     * @param string $role
     * @return User
     * @throws Throwable
     */
    public function create(UserDto $dto, string $role): User
    {
        try {
            DB::beginTransaction();

            $user = User::query()->make($dto->getCommonUserData());
            $user->status = User::STATUS_PENDING;

            if (Auth::user()->getRoleName() === User::DISPATCHER_ROLE) {
                $user->owner_id = Auth::id();
            } elseif (isset($dto->getCommonUserData()['owner_id'])) {
                $user->owner_id = $dto->getCommonUserData()['owner_id'];
            }

            $user->saveOrFail();

            $user->syncRoles($role);

            $this->updateAdditionalInfo($user, $dto);

            $this->addAttachments($user, $dto->getAttachments());

            if (!in_array($role, [User::BSMECHANIC_ROLE, User::OWNER_ROLE, User::OWNER_DRIVER_ROLE])) {
                $this->userNotificationService->send($user);
            }

            DB::commit();

            EventService::users($user)
                ->setLoggedUser($this->loggedUser)
                ->create()
                ->broadcast();

            return $user;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function updateAdditionalInfo(User $user, UserDto $dto): void
    {
        if ($user->isDriver()) {
            $this->updateDriverInfo($user, $dto->getDriverData());
        }

        if ($user->isOwner()) {
            $user->tags()->sync($dto->getTags());
        }
    }

    private function updateDriverInfo(User $user, DriverDto $dto): void
    {
        $user->driverInfo
            ? $user->driverInfo->update($dto->getDriverInfo())
            : $user->driverInfo()->create($dto->getDriverInfo());

        $user->load('driverInfo');

        if ($dto->getMedicalCardDocument()) {
            $user->driverInfo->addMediaWithRandomName(
                DriverInfo::ATTACHED_MEDICAL_CARD_FILED_NAME,
                $dto->getMedicalCardDocument(),
                true
            );
        }

        if ($dto->getMvrDocument()) {
            $user->driverInfo->addMediaWithRandomName(
                DriverInfo::ATTACHED_MVR_FILED_NAME,
                $dto->getMvrDocument(),
                true
            );
        }

        $this->fillDriverLicense(
            $user,
            DriverLicense::TYPE_CURRENT,
            $dto->getDriverLicenseData(),
            $dto->getDriverLicenseDocument()
        );

        $this->fillDriverLicense(
            $user,
            DriverLicense::TYPE_PREVIOUS,
            $dto->getPreviousDriverLicenseData(),
            $dto->getPreviousDriverLicenseDocument()
        );
    }

    private function fillDriverLicense(User $user, string $type, array $data, ?UploadedFile $file = null): void
    {
        $license = $user->driverLicenses()
            ->where('type', $type)
            ->first();

        if ($license) {
            $license->update($data);
        } else {
            $license = $user->driverLicenses()->create($data);
        }

        if ($file) {
            $license->addMediaWithRandomName(
                DriverLicense::ATTACHED_DOCUMENT_FILED_NAME,
                $file,
                true
            );
        }
    }

    /**
     * @param User $user
     * @param UserDto $dto
     * @param string $roleName
     * @return User
     * @throws Exception
     */
    public function update(User $user, UserDto $dto, string $roleName): User
    {
        try {
            DB::beginTransaction();

            $event = EventService::users($user)
                ->setLoggedUser($this->loggedUser);

            $user->update($dto->getCommonUserData());

            if ($roleName !== $user->getRoleName()) {
                $user->syncRoles($roleName);
            }

            $this->addAttachments($user, $dto->getAttachments());

            $this->updateAdditionalInfo($user, $dto);

            DB::commit();

            $event->update()
                ->broadcast();

            return $user;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function destroy(User $user): User
    {
        $user->update(['email' => $user->id]);
        $user->delete();

        EventService::users($user)
            ->setLoggedUser($this->loggedUser)
            ->delete()
            ->broadcast();

        return $user;
    }

    public function changeStatus(User $user): User
    {
        $event = EventService::users($user)
            ->setLoggedUser($this->loggedUser);

        $user->toggleStatus();

        $user->isActive() ? $event->activate() : $event->deactivate();

        $event->broadcast();

        return $user;
    }

    /**
     * @param User $user
     * @param UploadedFile $file
     * @return User
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function addAttachment(User $user, UploadedFile $file): User
    {
        try {
            $event = EventService::users($user)
                ->setLoggedUser($this->loggedUser);

            $user->addMediaWithRandomName(User::ATTACHMENT_COLLECTION_NAME, $file);

            $event->update(UserEventService::ACTION_FILE_ADD)->broadcast();

            return $user;
        } catch (Exception $e) {
            Log::error($e);
            throw $e;
        }
    }

    /**
     * @param User $user
     * @param array $attachments
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function addAttachments(User $user, array $attachments = []): void
    {
        foreach ($attachments as $attachment) {
            $this->addAttachment($user, $attachment);
        }
    }

    /**
     * @param User $user
     * @param int $mediaId
     * @throws MediaCannotBeDeleted
     * @throws Exception
     */
    public function deleteAttachment(User $user, $mediaId = 0)
    {
        if ($user->media->find($mediaId)) {

            $event = EventService::users($user)
                ->setLoggedUser($this->loggedUser);

            $user->deleteMedia($mediaId);

            $event->update(UserEventService::ACTION_FILE_DELETE)->broadcast();
            return;
        }

        throw new Exception(trans('File not found.'));
    }

    public function driverActiveOrders(User $user): Collection
    {
        return resolve(OrderSearchService::class)
            ->get(
                [
                    'driver_id' => $user->id,
                    'state' => [
                        Order::CALCULATED_STATUS_NEW,
                        Order::CALCULATED_STATUS_ASSIGNED,
                        Order::CALCULATED_STATUS_PICKED_UP,
                    ]
                ]
            );
    }

    public function driverHasActiveOrders(User $user): bool
    {
        return $this->driverActiveOrders($user)->isNotEmpty();
    }

    public function managerActiveOrders(User $user): Collection
    {
        return resolve(OrderSearchService::class)
            ->get(
                [
                    'dispatcher_id' => $user->id,
                    'state' => [
                        Order::CALCULATED_STATUS_NEW,
                        Order::CALCULATED_STATUS_ASSIGNED,
                        Order::CALCULATED_STATUS_PICKED_UP,
                    ]
                ]
            );
    }

    public function managerHasActiveOrders(User $user): bool
    {
        return $this->managerActiveOrders($user)->isNotEmpty();
    }

    public function managerHasAttachedDrivers(User $user): bool
    {
        return User::where('owner_id', $user->id)
            ->exists();
    }

    /**
     * @param User $dispatcherFrom
     * @param User $dispatcherTo
     * @throws Throwable
     */
    public function reassignDispatcherDrivers(User $dispatcherFrom, User $dispatcherTo): void
    {
        try {
            DB::beginTransaction();

            $drivers = User::filter(['owner' => $dispatcherFrom->id]);
            $reassignDrivers = $drivers->get();
            $drivers->update(['owner_id' => $dispatcherTo->id]);

            DB::commit();

            EventService::users($dispatcherTo)
                ->setLoggedUser($this->loggedUser)
                ->reassign($reassignDrivers)
                ->broadcast()
                ->push();

        } catch (Exception $e) {
            DB::rollBack();

            Log::error($e);

            throw $e;
        }
    }

    public function resendInvitationLink(User $user): void
    {
        $this->userNotificationService->send($user);
    }

    public function deleteDriverDocument(User $user, string $collectionName): void
    {
        $event = EventService::users($user)
            ->setLoggedUser($this->loggedUser);

        $user->driverInfo->clearMediaCollection($collectionName);

        $event->update(UserEventService::ACTION_FILE_DELETE);
    }

    public function deleteDriverLicenseDocument(User $user, ?DriverLicense $license): void
    {
        if ($license) {
            $event = EventService::users($user)
                ->setLoggedUser($this->loggedUser);

            $license->clearMediaCollection(DriverLicense::ATTACHED_DOCUMENT_FILED_NAME);

            $event->update(UserEventService::ACTION_FILE_DELETE);
        }
    }

    public function getHistoryShort(User $user)
    {
        $history = History::query()
            ->where(
                [
                    ['model_id', $user->id],
                    ['model_type', get_class($user)],
                ]
            )
            ->where('message', '!=', 'history.user_logged_in')
            ->latest('performed_at')
            ->get();

        return $this->applyHistoryTranslates($history);
    }

    public function getHistoryDetailed(User $user, UserHistoryRequest $request)
    {
        $history = History::query()
            ->where(
                [
                    ['model_id', $user->id],
                    ['model_type', get_class($user)],
                ]
            )
            ->whereType(History::TYPE_CHANGES)
            ->where('message', '!=', 'history.user_logged_in')
            ->filter($request->validated())
            ->latest('id')
            ->paginate($request->per_page);

        return $this->applyHistoryTranslates($history);
    }

    private function applyHistoryTranslates($history)
    {
        foreach ($history as &$h) {
            if (isset($h['meta']) && is_array($h['meta'])) {
                $h['message'] = trans($h['message'], $h['meta']);
            }
        }

        return $history;
    }

    public function getHistoryUsers(User $user)
    {
        return User::active()
            ->whereIn(
                'id',
                History::query()
                    ->select('user_id')
                    ->where(
                        [
                            ['model_id', $user->id],
                            ['model_type', get_class($user)],
                        ]
                    )
                    ->where('message', '!=', 'history.user_logged_in')
                    ->whereType(History::TYPE_CHANGES)
                    ->getQuery()
            )
            ->orderByRaw('concat(first_name, \' \', last_name) ASC')
            ->get();
    }
}
