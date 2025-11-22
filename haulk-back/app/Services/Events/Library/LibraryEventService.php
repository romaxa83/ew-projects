<?php


namespace App\Services\Events\Library;


use App\Broadcasting\Events\Library\CreateLibraryBroadcast;
use App\Broadcasting\Events\Library\DeleteLibraryBroadcast;
use App\Events\ModelChanged;
use App\Models\Library\LibraryDocument;
use App\Services\Events\EventService;

class LibraryEventService extends EventService
{
    private const HISTORY_MESSAGE_CONTACT_CREATE = 'history.library_created';
    private const HISTORY_MESSAGE_CONTACT_CREATE_BY_DRIVER = 'history.library_added_by_driver';
    private const HISTORY_MESSAGE_CONTACT_DELETE = 'history.library_deleted';

    private const BROADCASTING_EVENTS = [
        self::ACTION_CREATE => CreateLibraryBroadcast::class,
        self::ACTION_DELETE => DeleteLibraryBroadcast::class
    ];

    private LibraryDocument $document;

    private bool $createByDriver;

    public function __construct(LibraryDocument $document)
    {
        $this->document = $document;
    }

    private function getHistoryMessage(): ?string
    {
        switch ($this->action) {
            case self::ACTION_CREATE:
                return $this->createByDriver === false ? self::HISTORY_MESSAGE_CONTACT_CREATE : self::HISTORY_MESSAGE_CONTACT_CREATE_BY_DRIVER;
            case self::ACTION_DELETE:
                return self::HISTORY_MESSAGE_CONTACT_DELETE;
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
            'document' => $this->document->name
        ];
    }

    private function setHistory(): void
    {
        event(
            new ModelChanged(
                $this->document,
                $this->getHistoryMessage(),
                $this->getHistoryMeta(),
            )
        );
    }

    public function create(bool $byDriver = false): LibraryEventService
    {
        parent::create();

        $this->createByDriver = $byDriver;

        $this->setHistory();

        return $this;
    }

    public function delete(): LibraryEventService
    {
        parent::delete();

        $this->setHistory();

        return $this;
    }

    public function broadcast(): LibraryEventService
    {
        $broadcast = self::BROADCASTING_EVENTS[$this->action];

        event(new $broadcast($this->document->id, $this->user->getCompanyId()));

        return $this;
    }

}
