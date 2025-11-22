<?php


namespace App\Services\Events\User;

use App\Broadcasting\Events\User\ActivateUserBroadcast;
use App\Broadcasting\Events\User\CreateUserBroadcast;
use App\Broadcasting\Events\User\DeactivateUserBroadcast;
use App\Broadcasting\Events\User\DeleteUserBroadcast;
use App\Broadcasting\Events\User\UpdateUserBroadcast;
use App\Events\ModelChanged;
use App\Models\Users\User;
use App\Services\Events\EventService;
use App\Services\Histories\UserHistoryHandler;
use Illuminate\Database\Eloquent\Collection;

class UserEventService extends EventService
{
    private const ACTION_ACTIVATE = 'activate';
    private const ACTION_DEACTIVATE = 'deactivate';
    private const ACTION_REASSIGN_DRIVERS = 'reassign_drivers';

    public const ACTION_FILE_ADD = 'attachment_added';
    public const ACTION_FILE_DELETE = 'attachment_deleted';
    public const ACTION_COMMENT_ADD = 'comment_added';
    public const ACTION_COMMENT_DELETE = 'comment_deleted';

    private const HISTORY_MESSAGE_CONTACT_CREATE = 'history.user_created';
    private const HISTORY_MESSAGE_CONTACT_UPDATE = 'history.user_updated';
    private const HISTORY_MESSAGE_CONTACT_DELETE = 'history.user_deleted';
    private const HISTORY_MESSAGE_FILE_ADD = 'history.user_file_added';
    private const HISTORY_MESSAGE_FILE_DELETE = 'history.user_file_deleted';
    private const HISTORY_MESSAGE_COMMENT_CREATE = 'history.user_comment_created';
    private const HISTORY_MESSAGE_COMMENT_DELETE = 'history.user_comment_deleted';
    private const HISTORY_MESSAGE_USER_ACTIVATED = 'history.user_activated';
    private const HISTORY_MESSAGE_USER_DEACTIVATED = 'history.user_deactivated';

    private const BROADCASTING_EVENTS = [
        self::ACTION_UPDATE => UpdateUserBroadcast::class,
        self::ACTION_CREATE => CreateUserBroadcast::class,
        self::ACTION_DELETE => DeleteUserBroadcast::class,
        self::ACTION_ACTIVATE => ActivateUserBroadcast::class,
        self::ACTION_DEACTIVATE => DeactivateUserBroadcast::class,
        self::ACTION_FILE_ADD => UpdateUserBroadcast::class,
        self::ACTION_FILE_DELETE => UpdateUserBroadcast::class,
    ];

    private Collection $drivers;

    private User $changedUser;

    private User $changedUserForHandler;

    private ?UserHistoryHandler $historyHandler = null;

    private User $loggedUser;

    public function __construct(?User $user = null)
    {
        if ($user === null) {
            return;
        }
        $this->user = $user;


        $this->changedUser = $user;
        $this->changedUserForHandler = clone $user;

        try {
            $this->historyHandler = (new UserHistoryHandler())->setOrigin($this->changedUserForHandler);
        } catch (TypeError $e) {
            $this->historyHandler = null;
        }
    }

    public function setLoggedUser(User $user): EventService
    {
        $this->loggedUser = $user;

        return $this;
    }

    private function getHistoryMessage(): ?string
    {
        switch ($this->action) {
            case self::ACTION_CREATE:
                return self::HISTORY_MESSAGE_CONTACT_CREATE;
            case self::ACTION_UPDATE:
                return self::HISTORY_MESSAGE_CONTACT_UPDATE;
            case self::ACTION_DELETE:
                return self::HISTORY_MESSAGE_CONTACT_DELETE;
            case self::ACTION_FILE_ADD:
                return self::HISTORY_MESSAGE_FILE_ADD;
            case self::ACTION_FILE_DELETE:
                return self::HISTORY_MESSAGE_FILE_DELETE;
            case self::ACTION_COMMENT_ADD:
                return self::HISTORY_MESSAGE_COMMENT_CREATE;
            case self::ACTION_COMMENT_DELETE:
                return self::HISTORY_MESSAGE_COMMENT_DELETE;
            case self::ACTION_ACTIVATE:
                return self::HISTORY_MESSAGE_USER_ACTIVATED;
            case self::ACTION_DEACTIVATE:
                return self::HISTORY_MESSAGE_USER_DEACTIVATED;
        }
        return null;
    }

    private function getHistoryMeta(): array
    {
        return [
            'role' => $this->loggedUser->getRoleName(),
            'full_name' => $this->loggedUser->full_name,
            'email' => $this->loggedUser->email,
            'user_id' => $this->user->id,
            'changed_user_role' => $this->changedUser->getRoleName(),
        ];
    }

    private function setHistory(): void
    {
        $message = $this->getHistoryMessage();

        if (!$this->isUserUpdated($message)) {
            return;
        }

        event(
            new ModelChanged(
                $this->user,
                $message,
                $this->getHistoryMeta(),
                null,
                null,
                $this->historyHandler
            )
        );
    }

    public function create(): UserEventService
    {
        parent::create();

        if ($this->historyHandler) {
            $this->historyHandler->setOrigin(null);
            $this->historyHandler->setDirty($this->changedUserForHandler);
        }

        $this->setHistory();

        return $this;
    }

    public function update(string $action = null): UserEventService
    {
        parent::update();

        if ($action) {
            $this->action = $action;
        }

        if ($this->historyHandler) {
            $this->historyHandler->setOrigin($this->changedUserForHandler);
        }

        $this->refreshObject();

        $this->setHistory();

        return $this;
    }

    public function delete(): UserEventService
    {
        parent::delete();

        $this->setHistory();

        return $this;
    }

    public function activate(): UserEventService
    {
        $this->action = self::ACTION_ACTIVATE;

        if ($this->historyHandler) {
            $this->historyHandler->setOrigin($this->changedUserForHandler);
        }

        $this->refreshObject();

        $this->setHistory();

        return $this;
    }

    public function deactivate(): UserEventService
    {
        $this->action = self::ACTION_DEACTIVATE;

        if ($this->historyHandler) {
            $this->historyHandler->setOrigin($this->changedUserForHandler);
        }

        $this->refreshObject();

        $this->setHistory();

        return $this;
    }

    public function reassign(Collection $drivers): UserEventService
    {
        $this->action = self::ACTION_UPDATE;

        $dispatcher = $this->user;

        $drivers->map(
            function (User $user) {
                $this->user = $user;

                event(
                    new ModelChanged(
                        $this->user,
                        $this->getHistoryMessage(),
                        $this->getHistoryMeta()
                    )
                );
            }
        );

        $this->drivers = $drivers;

        $this->user = $dispatcher;

        $this->action = self::ACTION_REASSIGN_DRIVERS;

        return $this;
    }

    public function broadcast(): UserEventService
    {
        if ($this->action === self::ACTION_REASSIGN_DRIVERS) {
            $this->drivers->map(
                function (User $user) {
                    event(new UpdateUserBroadcast($user->id, $user->getCompanyId()));
                }
            );
            return $this;
        }

        $broadcast = self::BROADCASTING_EVENTS[$this->action] ?? null;

        if ($broadcast) {
            event(new $broadcast($this->user->id, $this->user->getCompanyId()));
        }

        return $this;
    }

    public function push(): UserEventService
    {
        switch ($this->action) {
            case self::ACTION_REASSIGN_DRIVERS:
                return $this->pushReassignDrivers();
        }

        return $this;
    }

    private function pushReassignDrivers(): UserEventService
    {
        $this->pushService()
            ->onReassignDispatcherDrivers($this->user, $this->drivers);

        return $this;
    }

    private function refreshObject(): void
    {
        $this->changedUser->refresh();

        if ($this->historyHandler === null) {
            return;
        }

        $this->historyHandler->setDirty($this->changedUser);
    }

    private function isUserUpdated(string $message): bool
    {
        if ($message === self::HISTORY_MESSAGE_CONTACT_CREATE) {
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
}
