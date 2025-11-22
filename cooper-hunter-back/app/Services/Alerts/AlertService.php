<?php

namespace App\Services\Alerts;

use App\Contracts\Alerts\AlertModel;
use App\Contracts\Alerts\MetaDataDto;
use App\Contracts\Members\Member;
use App\Contracts\Roles\HasGuardUser;
use App\Dto\Alerts\AlertSendDto;
use App\Dto\Alerts\Fcm\FcmData;
use App\Dto\Alerts\MetaData\SupportRequestMessageDto;
use App\Dto\Alerts\MetaData\TechnicianDto;
use App\Dto\Alerts\MetaData\UserDto;
use App\Enums\Alerts\AlertDealerEnum;
use App\Enums\Alerts\AlertModelEnum;
use App\Enums\Alerts\AlertOrderEnum;
use App\Enums\Alerts\AlertSupportRequestEnum;
use App\Enums\Alerts\AlertSystemEnum;
use App\Enums\Alerts\AlertTechnicianEnum;
use App\Enums\Alerts\AlertUserEnum;
use App\Enums\Orders\OrderStatusEnum;
use App\GraphQL\Subscriptions\BackOffice\Alerts\AlertSubscription as BackAlertSubscription;
use App\GraphQL\Subscriptions\FrontOffice\Alerts\AlertSubscription as FrontAlertSubscription;
use App\Models\Admins\Admin;
use App\Models\Alerts\Alert;
use App\Models\Alerts\AlertRecipient;
use App\Models\Dealers\Dealer;
use App\Models\Orders\Order;
use App\Models\Projects\System;
use App\Models\Support\SupportRequest;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use App\Notifications\Alerts\AlertFcmNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AlertService
{
    private ?HasGuardUser $initiator;

    private null|MetaDataDto|TechnicianDto|SupportRequestMessageDto|UserDto $metaData;

    public function setInitiator(?HasGuardUser $initiator): AlertService
    {
        $this->initiator = $initiator;

        return $this;
    }

    public function setMetaData(?MetaDataDto $metaData): AlertService
    {
        $this->metaData = $metaData;

        return $this;
    }

    public function create(AlertModel $model): void
    {
        match ($model->getMorphType()) {
            AlertModelEnum::ORDER => $this->createOrderAlert($model),
            AlertModelEnum::SUPPORT_REQUEST => $this->createSupportRequestAlert($model),
            AlertModelEnum::TECHNICIAN => $this->createTechnicianAlert($model),
            AlertModelEnum::SYSTEM => $this->createSystemAlert($model),
            AlertModelEnum::USER => $this->createUserAlert($model),
            AlertModelEnum::DEALER => $this->createDealerAlert($model),
        };
    }

    private function createOrderAlert(AlertModel|Order $order): bool
    {
        if ($order->wasRecentlyCreated && $this->initiator instanceof Technician) {
            $this->createAlert(
                Admin::all(),
                AlertModelEnum::ORDER,
                AlertOrderEnum::CREATE,
                $order,
                [
                    'description' => [
                        'email' => (string)$order->technician->email
                    ]
                ]
            );
            return true;
        }

        $this->createAlert(
            $order->technician,
            AlertModelEnum::ORDER,
            AlertOrderEnum::CHANGE_STATUS,
            $order,
            [
                'description' => [
                    'status' => OrderStatusEnum::getLocalizationKey() . '.' . $order->status
                ]
            ]
        );

        return true;
    }

    private function createSupportRequestAlert(AlertModel|SupportRequest $supportRequest): bool
    {
        if ($supportRequest->is_closed === true) {
            $this->createAlert(
                $supportRequest->technician,
                AlertModelEnum::SUPPORT_REQUEST,
                AlertSupportRequestEnum::CLOSE,
                $supportRequest
            );
            return true;
        }

        $message = $this->metaData->getMessage();

        if ($message->sender_type === Admin::MORPH_NAME) {
            $this->createAlert(
                $supportRequest->technician,
                AlertModelEnum::SUPPORT_REQUEST,
                AlertSupportRequestEnum::NEW_MESSAGE,
                $supportRequest,
                [
                    'description' => [
                        'message' => $message->message
                    ]
                ]
            );
            return true;
        }

        $adminIds = $supportRequest->messages()
            ->select('sender_id')
            ->where('sender_type', Admin::MORPH_NAME)
            ->groupBy('sender_id')
            ->get()
            ->pluck('sender_id');

        if ($adminIds->isEmpty()) {
            if ($supportRequest->messages->count() === 1) {
                $this->createAlert(
                    Admin::all(),
                    AlertModelEnum::SUPPORT_REQUEST,
                    AlertSupportRequestEnum::NEW_REQUEST,
                    $supportRequest,
                    [
                        'description' => [
                            'message' => $message->message
                        ]
                    ]
                );
            }
            return true;
        }
        Admin::whereIn('id', $adminIds)
            ->get()
            ->each(
                fn(Admin $admin) => $this->createAlert(
                    $admin,
                    AlertModelEnum::SUPPORT_REQUEST,
                    AlertSupportRequestEnum::NEW_MESSAGE,
                    $supportRequest,
                    [
                        'description' => [
                            'message' => $message->message
                        ]
                    ]
                )
            );

        return true;
    }

    private function createTechnicianAlert(AlertModel|Technician $technician): bool
    {
        if ($this->metaData->isChangeModerationStatus()) {
            return $this->createModerationTechnicianAlert($technician);
        }

        if ($this->metaData->isChangeEmailVerificationStatus()) {
            return $this->createEmailVerificationTechnicianAlert($technician);
        }

        return $this->createMemberRegistrationEvent(
            AlertModelEnum::TECHNICIAN,
            AlertTechnicianEnum::REGISTRATION,
            $technician
        );
    }

    private function createModerationTechnicianAlert(Technician $technician): bool
    {
        if ($technician->is_verified) {
            $this->createAlert(
                $technician,
                AlertModelEnum::TECHNICIAN,
                AlertTechnicianEnum::MODERATION_READY,
                $technician
            );
            return true;
        }

        $this->createAlert(
            $technician,
            AlertModelEnum::TECHNICIAN,
            AlertTechnicianEnum::RE_MODERATION,
            $technician
        );
        $this->createAlert(
            Admin::all(),
            AlertModelEnum::TECHNICIAN,
            AlertTechnicianEnum::NEW_RE_MODERATION,
            $technician,
            [
                'description' => [
                    'email' => (string)$technician->email
                ]
            ],
        );
        return true;
    }

    private function createEmailVerificationTechnicianAlert(Technician $technician): bool
    {
        if ($technician->isEmailVerified()) {
            $this->createAlert(
                $technician,
                AlertModelEnum::TECHNICIAN,
                AlertTechnicianEnum::EMAIL_VERIFICATION_READY,
                $technician
            );
            return true;
        }

        $this->createAlert(
            $technician,
            AlertModelEnum::TECHNICIAN,
            AlertTechnicianEnum::EMAIL_VERIFICATION_PROCESS,
            $technician
        );

        return true;
    }

    private function createMemberRegistrationEvent(string $type, string $subtype, Member $member): bool
    {
        $this->createAlert(
            Admin::all(),
            $type,
            $subtype,
            $member,
            [
                'description' => [
                    'email' => (string)$member->getEmail()
                ]
            ]
        );
        return true;
    }

    private function createSystemAlert(AlertModel|System $system): bool
    {
        if (empty($system->warrantyRegistration)) {
            //warranty can be registered separately from the system
            return true;
        }

        $this->createAlert(
            $system->warrantyRegistration->member,
            AlertModelEnum::SYSTEM,
            AlertSystemEnum::WARRANTY_STATUS,
            $system,
            [
                'description' => [
                    'status' => $system->warranty_status
                ]
            ]
        );
        return true;
    }

    private function createUserAlert(AlertModel|User $user): bool
    {
        return $this->createMemberRegistrationEvent(AlertModelEnum::USER, AlertUserEnum::REGISTRATION, $user);
    }

    private function createDealerAlert(AlertModel|Dealer $dealer): bool
    {
        return $this->createMemberRegistrationEvent(
            AlertModelEnum::DEALER,
            AlertDealerEnum::REGISTRATION,
            $dealer
        );
    }

    private function createAlert(
        Collection|Technician|User|Admin|Dealer $user,
        string $type,
        string $subtype,
        AlertModel|Member $model,
        ?array $meta = null
    ): array
    {
        $result = [
            'title' => 'alerts.' . $type . '.' . $subtype . '.title',
            'description' => 'alerts.' . $type . '.' . $subtype . '.description',
            'type' => $type . '_' . $subtype,
            'model_id' => $model->getId(),
            'model_type' => $model->getMorphType()
        ];

        if (!empty($meta)) {
            $result['meta'] = $meta;
        }

        $alert = new Alert();
        $alert->fill($result);
        $alert->save();

        $alert->recipients()
            ->insert(
                $user instanceof Collection ? $user->map(
                    fn(AlertModel $model) => [
                        'alert_id' => $alert->id,
                        'recipient_id' => $model->getId(),
                        'recipient_type' => $model->getMorphType()
                    ]
                )
                    ->toArray() : [
                    [
                        'alert_id' => $alert->id,
                        'recipient_id' => $user->getId(),
                        'recipient_type' => $user->getMorphType()
                    ]
                ]
            );

        if ($user instanceof Collection) {
            $notify = BackAlertSubscription::notify();
        } elseif ($user instanceof Admin) {
            $notify = BackAlertSubscription::notify()
                ->toUser($user);
        } else {
            $notify = FrontAlertSubscription::notify()
                ->toUser($user);
        }

        $notify
            ->withContext(
                [
                    'alert' => $alert->id
                ]
            )
            ->broadcast();

        if (!$user instanceof Collection) {
            $user->notify(
                new AlertFcmNotification(
                    FcmData::init($alert)
                )
            );
        }

        return $result;
    }

    public function sendCustomAlert(AlertSendDto $dto): bool
    {
        $usersIds = $technicianIds = [];
        foreach ($dto->getRecipients() as $recipient) {
            if ($recipient->getType()
                ->isTechnician()) {
                $technicianIds[] = $recipient->getId();
            } else {
                $usersIds[] = $recipient->getId();
            }
        }

        User::query()
            ->whereIn('id', $usersIds)
            ->get()
            ->map(
                fn(User $user) => $this->createCustomAlert($user, $dto)
            );

        Technician::query()
            ->whereIn('id', $technicianIds)
            ->get()
            ->map(
                fn(Technician $technician) => $this->createCustomAlert($technician, $dto)
            );
        return true;
    }

    private function createCustomAlert(User|Technician $member, AlertSendDto $dto): void
    {
        $alert = new Alert(
            [
                'title' => $dto->getTitle(),
                'description' => $dto->getDescription(),
                'type' => $member->getMorphType() . '_' . AlertUserEnum::CUSTOM,
                'model_id' => $member->id,
                'model_type' => $member->getMorphType(),
                'meta' => [
                    'title' => [
                        'name' => $member->getName()
                    ],
                    'description' => [
                        'name' => $member->getName()
                    ]
                ]
            ]
        );
        $alert->save();
        $alert->recipients()
            ->create(
                [
                    'recipient_id' => $member->id,
                    'recipient_type' => $member->getMorphType()
                ]
            );

        FrontAlertSubscription::notify()
            ->toUser($member)
            ->withContext(
                [
                    'alert' => $alert->id
                ]
            )
            ->broadcast();

        $member->notify(
            new AlertFcmNotification(
                FcmData::init($alert)
            )
        );
    }

    public function getList(array $args, Admin|User|Technician|Dealer $user): LengthAwarePaginator
    {
        /**@var \Illuminate\Pagination\LengthAwarePaginator $paginate */
        $paginate = $user->alerts()
            ->filter($args)
            ->orderByDesc('id')
            ->paginate(perPage: $args['per_page'], page: $args['page']);

        $paginate = $paginate->through(
            fn(Alert $alert) => [
                'id' => $alert->id,
                'title' => trans(
                    $alert->title,
                    !empty($alert->meta['title']) ?
                        array_map('trans', $alert->meta['title']) :
                        []
                ),
                'description' => trans(
                    $alert->description,
                    !empty($alert->meta['description']) ?
                        array_map('trans', $alert->meta['description']) :
                        []
                ),
                'type' => $alert->type,
                'is_read' => (bool)$alert->pivot->is_read,
                'object' => [
                    'id' => $alert->model_id,
                    'name' => $alert->model_type
                ],
                'created_at' => $alert->created_at->getTimestamp(),
            ]
        );

        return $paginate;
    }

    public function setRead(?array $ids, Admin|User|Technician $user): bool
    {
        $query = AlertRecipient::query()
            ->where('recipient_id', $user->getId())
            ->where('recipient_type', $user->getMorphType());

        if (!empty($ids)) {
            $query = $query->whereIn('alert_id', $ids);
        }

        $query->update(['is_read' => true]);
        return true;
    }

    public function getCounter(array $args, Admin|User|Technician|Dealer $user): object
    {
        $query = $user
            ->alerts()
            ->filter($args);

        return DB::table(
            DB::raw(
                "(" . $query->toSql() . ") AS alerts"
            )
        )
            ->setBindings(
                $query->getBindings()
            )
            ->selectRaw(
                "
                    SUM(IF(is_read=1,0,1)) AS not_read,
                    COUNT(*) AS total
                "
            )
            ->get()
            ->transform(
                fn(object $item) => (object)[
                    'not_read' => $item->not_read !== null ? $item->not_read : 0,
                    'total' => $item->total !== null ? $item->total : 0,
                ]
            )
            ->first();
    }

    public function getMemberForAlert(array $args): LengthAwarePaginator
    {
        $userQuery = User::filter($args)
            ->select(['id', 'first_name', 'last_name', 'email', 'phone', 'created_at'])
            ->addSelect(DB::raw('\'' . User::MORPH_NAME . '\' AS type'));
        $technicianQuery = Technician::filter($args)
            ->select(['id', 'first_name', 'last_name', 'email', 'phone', 'created_at'])
            ->addSelect(DB::raw('\'' . Technician::MORPH_NAME . '\' AS type'));
        $dealerQuery = Dealer::filter($args)
            ->select(['id', 'first_name', 'last_name', 'email', 'phone', 'created_at'])
            ->addSelect(DB::raw('\'' . Dealer::MORPH_NAME . '\' AS type'))
        ;
        if (empty($args['member_type'])) {
            $query = $userQuery
                ->union($technicianQuery)
                ->union($dealerQuery)
            ;
        } else {
            $query = match($args['member_type']) {
                User::MORPH_NAME => $userQuery,
                Technician::MORPH_NAME => $technicianQuery,
                Dealer::MORPH_NAME => $dealerQuery
            };
        }
        return $query
            ->orderByDesc('created_at')
            ->paginate(perPage: $args['per_page'], page: $args['page']);
    }
}
