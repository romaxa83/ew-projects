<?php

namespace App\Services\Contacts;

use App\Dto\Contacts\ContactDto;
use App\Models\Contacts\Contact;
use App\Models\Users\User;
use App\Services\Events\EventService;
use Illuminate\Support\Carbon;
use Throwable;

class ContactService
{
    private ?User $user = null;

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getContactTypesForContacts(): array
    {
        $data = [];

        foreach (Contact::CONTACT_TYPES as $k => $v) {
            $data[] = [
                'id' => $k,
                'title' => $v,
            ];
        }

        return $data;
    }

    public function getContactTypesForOrder(): array
    {
        $data = [];

        foreach (Contact::CONTACT_TYPES as $k => $v) {
            $data[] = [
                'id' => $k,
                'title' => $v,
            ];
        }

        return $data;
    }

    /**
     * @param ContactDto $dto
     * @return Contact
     * @throws Throwable
     */
    public function create(ContactDto $dto): Contact
    {
        $contact = $this->fill(Contact::make()->setUser($this->user), $dto);

        EventService::contact($contact)
            ->user($this->user)
            ->create()
            ->broadcast();

        return $contact;
    }

    /**
     * @param Contact $contact
     * @param ContactDto $dto
     * @return Contact
     * @throws Throwable
     */
    public function update(Contact $contact, ContactDto $dto): Contact
    {
        $contact = $this->fill($contact, $dto);

        EventService::contact($contact)
            ->user($this->user)
            ->update()
            ->broadcast();

        return $contact;
    }

    /**
     * @param Contact $contact
     * @param ContactDto $dto
     * @return Contact
     * @throws Throwable
     */
    private function fill(Contact $contact, ContactDto $dto): Contact
    {
        $contact->fill($dto->toArray());
        if ($contact->isDirty('comment')) {
            $contact->comment_date = Carbon::now()->getTimestamp();
        }
        $contact->saveOrFail();
        return $contact;
    }

    public function delete(Contact $contact): void
    {
        $contact->delete();

        EventService::contact($contact)
            ->user($this->user)
            ->delete()
            ->broadcast();
    }
}
