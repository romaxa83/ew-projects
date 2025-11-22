<?php

namespace App\Rules\Contacts;

use App\Models\Contacts\Contact;
use Illuminate\Contracts\Validation\Rule;

class UniqEmailRule implements Rule
{
    private bool $willBeSaved;
    private string $contactName;
    private string $email;
    private int $contactId = 0;

    public function __construct(bool $willBeSaved)
    {
        $contact = request()->route()->parameter('contact');
        if ($contact instanceof Contact) {
            $this->contactId = $contact->id;
        }
        $this->willBeSaved = $willBeSaved;
    }

    public function passes($attribute, $value): bool
    {
        if (!$this->willBeSaved) {
            return true;
        }
        $contact = Contact::whereEmail($value)->where('id', '<>', $this->contactId)->first();
        if (!$contact) {
            return true;
        }
        $this->email = $value;
        $this->contactName = $contact->full_name;
        return false;
    }

    public function message(): string
    {
        return sprintf("This email [%s] already exists. It belongs to [%s].", $this->email, $this->contactName);
    }
}
