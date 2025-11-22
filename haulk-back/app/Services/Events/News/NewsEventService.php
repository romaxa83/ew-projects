<?php


namespace App\Services\Events\News;

use App\Broadcasting\Events\News\ActivateNewsBroadcast;
use App\Broadcasting\Events\News\CreateNewsBroadcast;
use App\Broadcasting\Events\News\DeactivateNewsBroadcast;
use App\Broadcasting\Events\News\DeleteNewsBroadcast;
use App\Broadcasting\Events\News\UpdateNewsBroadcast;
use App\Events\ModelChanged;
use App\Models\News\News;
use App\Notifications\Alerts\AlertNotification;
use App\Services\Events\EventService;
use Lang;

class NewsEventService extends EventService
{
    private const ACTION_ACTIVATE = 'activate';
    private const ACTION_DEACTIVATE = 'deactivate';

    private const HISTORY_MESSAGE_NEWS_CREATE = 'history.news_created';
    private const HISTORY_MESSAGE_NEWS_UPDATE = 'history.news_updated';
    private const HISTORY_MESSAGE_NEWS_DELETE = 'history.news_deleted';
    private const HISTORY_MESSAGE_NEWS_ACTIVATE = 'history.news_activated';
    private const HISTORY_MESSAGE_NEWS_DEACTIVATE = 'history.news_deactivated';

    private const BROADCASTING_EVENTS = [
        self::ACTION_UPDATE => UpdateNewsBroadcast::class,
        self::ACTION_CREATE => CreateNewsBroadcast::class,
        self::ACTION_DELETE => DeleteNewsBroadcast::class,
        self::ACTION_ACTIVATE => ActivateNewsBroadcast::class,
        self::ACTION_DEACTIVATE => DeactivateNewsBroadcast::class
    ];

    private News $news;

    public function __construct(News $news)
    {
        $this->news = $news;
    }

    private function getHistoryMessage(): ?string
    {
       switch ($this->action) {
           case self::ACTION_CREATE:
               return self::HISTORY_MESSAGE_NEWS_CREATE;
           case self::ACTION_UPDATE:
               return self::HISTORY_MESSAGE_NEWS_UPDATE;
           case self::ACTION_DELETE:
               return self::HISTORY_MESSAGE_NEWS_DELETE;
           case self::ACTION_ACTIVATE:
               return self::HISTORY_MESSAGE_NEWS_ACTIVATE;
           case self::ACTION_DEACTIVATE:
               return self::HISTORY_MESSAGE_NEWS_DEACTIVATE;
       }
       return null;
    }

    private function getHistoryMeta(): array
    {
        return [
            'role' => $this->user->getRoleName(),
            'full_name' => $this->user->full_name,
            'email' => $this->user->email,
            'user_id' => $this->user->id,
            'news_title' => $this->news->title_en
        ];
    }

    private function setHistory(): void
    {
        event(
            new ModelChanged(
                $this->news,
                $this->getHistoryMessage(),
                $this->getHistoryMeta(),
            )
        );
    }

    public function create(): NewsEventService
    {
        parent::create();

        $this->setHistory();

        return $this;
    }

    public function update(): NewsEventService
    {
        parent::update();

        $this->setHistory();

        return $this;
    }

    public function delete(): NewsEventService
    {
        parent::delete();

        $this->setHistory();

        return $this;
    }

    public function activate(): NewsEventService
    {
        $this->action = self::ACTION_ACTIVATE;

        $this->setHistory();

        return $this;
    }

    public function deactivate(): NewsEventService
    {
        $this->action = self::ACTION_DEACTIVATE;

        $this->setHistory();

        return $this;
    }

    public function broadcast(): NewsEventService
    {
        $broadcast = self::BROADCASTING_EVENTS[$this->action];

        event(new $broadcast($this->news->id, $this->user->getCompanyId()));

        if ($this->action === self::ACTION_CREATE) {
            $this->user->getCompany()->notify(
                new AlertNotification(
                    $this->user->getCompanyId(),
                    $this->getHistoryMessage(),
                    AlertNotification::TARGET_TYPE_NEWS,
                    ['news_id' => $this->news->id,],
                    $this->getHistoryMeta()
                )
            );
        }

        return $this;
    }
}
