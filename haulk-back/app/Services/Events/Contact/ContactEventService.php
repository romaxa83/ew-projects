<?php


namespace App\Services\Events\Contact;

use App\Broadcasting\Events\Contact\CreateContactBroadcast;
use App\Broadcasting\Events\Contact\DeleteContactBroadcast;
use App\Broadcasting\Events\Contact\UpdateContactBroadcast;
use App\Events\ModelChanged;
use App\Models\Contacts\Contact;
use App\Services\Events\EventService;

class ContactEventService extends EventService
{

    private const HISTORY_MESSAGE_CONTACT_CREATE = 'history.contact_created';
    private const HISTORY_MESSAGE_CONTACT_UPDATE = 'history.contact_updated';
    private const HISTORY_MESSAGE_CONTACT_DELETE = 'history.contact_deleted';

    private const BROADCASTING_EVENTS = [
        self::ACTION_UPDATE => UpdateContactBroadcast::class,
        self::ACTION_CREATE => CreateContactBroadcast::class,
        self::ACTION_DELETE => DeleteContactBroadcast::class
    ];

    private Contact $contact;

    public function __construct(Contact $contact)
    {
        $this->contact = $contact;
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
            'contact_full_name' => $this->contact->full_name
        ];
    }

    private function setHistory(): void
    {
        event(
            new ModelChanged(
                $this->contact,
                $this->getHistoryMessage(),
                $this->getHistoryMeta(),
            )
        );
    }

    public function create(): ContactEventService
    {
        parent::create();

        $this->setHistory();

        return $this;
    }

    public function update(): ContactEventService
    {
        parent::update();

        $this->setHistory();

        return $this;
    }

    public function delete(): ContactEventService
    {
        parent::delete();

        $this->setHistory();

        return $this;
    }

    public function broadcast(): ContactEventService
    {
        $broadcast = self::BROADCASTING_EVENTS[$this->action];

        event(new $broadcast($this->contact->id, $this->user->getCompanyId()));

        return $this;
    }
}
