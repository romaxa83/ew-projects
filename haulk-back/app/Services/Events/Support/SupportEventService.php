<?php


namespace App\Services\Events\Support;


use App\Broadcasting\Events\Support\Backoffice\ChangeRequestLabelBroadcast;
use App\Broadcasting\Events\Support\Backoffice\ChangeRequestStatusBroadcast as ChangeRequestStatusBroadcastBackoffice;
use App\Broadcasting\Events\Support\Backoffice\NewIsNotReadMessageBroadcast as NewIsNotReadMessageBroadcastBackoffice;
use App\Broadcasting\Events\Support\Backoffice\NewIsNotViewRequestBroadcast;
use App\Broadcasting\Events\Support\Backoffice\NewMessageBroadcast as NewMessageBroadcastBackoffice;
use App\Broadcasting\Events\Support\Backoffice\NewRequestBroadcast as NewRequestBroadcastBackoffice;
use App\Broadcasting\Events\Support\Crm\NewIsNotReadMessageBroadcast as NewIsNotReadMessageBroadcastCrm;
use App\Broadcasting\Events\Support\Crm\ChangeRequestStatusBroadcast as ChangeRequestStatusBroadcastCrm;
use App\Broadcasting\Events\Support\Crm\NewMessageBroadcast as NewMessageBroadcastCrm;
use App\Broadcasting\Events\Support\Crm\NewRequestBroadcast as NewRequestBroadcastCrm;
use App\Models\Admins\Admin;
use App\Models\Saas\Support\SupportRequest;
use App\Models\Saas\Support\SupportRequestMessage;
use App\Models\Users\User;
use App\Scopes\CompanyScope;
use App\Services\Events\EventService;

class SupportEventService extends EventService
{

    private const ACTION_NEW_MESSAGE = 'new_message';
    private const ACTION_CHANGE_STATUS = 'change_status';
    private const ACTION_CHANGE_LABEL = 'change_label';

    private SupportRequest $supportRequest;

    private SupportRequestMessage $supportRequestMessage;

    public function __construct(SupportRequest $supportRequest)
    {
        $this->supportRequest = $supportRequest;
    }

    public function status(): SupportEventService
    {
        $this->action = self::ACTION_CHANGE_STATUS;

        return $this;
    }

    public function label(): SupportEventService
    {
        $this->action = self::ACTION_CHANGE_LABEL;

        return $this;
    }

    public function message(SupportRequestMessage $supportRequestMessage): SupportEventService
    {
        $this->action = self::ACTION_NEW_MESSAGE;

        $this->supportRequestMessage = $supportRequestMessage;

        return $this;
    }

    public function broadcast(): SupportEventService
    {
        if ($this->user instanceof Admin) {
            $admin = $this->user;
            $this->user = $this->supportRequest->user;
        } else {
            $admin = $this->supportRequest->admin;
        }
        switch ($this->action) {
            case self::ACTION_CREATE:
                event(new NewRequestBroadcastBackoffice($this->supportRequest->id, $admin));
                if ($this->user !== null) {
                    event(new NewRequestBroadcastCrm($this->supportRequest->id, $this->user));
                }
                return $this;
            case self::ACTION_NEW_MESSAGE:
                event(new NewMessageBroadcastBackoffice($this->supportRequest->id, $this->supportRequestMessage->id, $admin));
                if ($this->user !== null) {
                    event(new NewMessageBroadcastCrm($this->supportRequest->id, $this->supportRequestMessage->id, $this->user));
                }

                if ($this->supportRequestMessage->user_id === null && $this->user !== null) {
                    $this->supportRequest
                        ->messages()
                        ->select('user_id')
                        ->whereNotNull('user_id')
                        ->groupBy('user_id')
                        ->get()
                        ->pluck('user_id')
                        ->map(
                            function (int $id) {
                                $user = User::withoutGlobalScope(CompanyScope::class)->find($id);

                                if ($user) {
                                    event(
                                        new NewIsNotReadMessageBroadcastCrm(
                                            $this->supportRequest->id,
                                            $this->supportRequestMessage->id,
                                            $user
                                        )
                                    );
                                }
                            }
                        );
                } else {
                    if ($admin === null) {
                        event(new NewIsNotViewRequestBroadcast($this->supportRequestMessage->id, $this->supportRequestMessage->id));
                    } else {
                        event(new NewIsNotReadMessageBroadcastBackoffice($this->supportRequestMessage->id, $this->supportRequestMessage->id, $admin));
                    }
                }
                return $this;
            case self::ACTION_CHANGE_STATUS:
                event(new ChangeRequestStatusBroadcastBackoffice($this->supportRequest->id));
                if ($this->user !== null) {
                    event(new ChangeRequestStatusBroadcastCrm($this->supportRequest->id, $this->user));
                }
                return $this;
            case self::ACTION_CHANGE_LABEL:
                event(new ChangeRequestLabelBroadcast($this->supportRequest->id));
                return $this;
        }
        return $this;
    }

}
