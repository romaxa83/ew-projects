<?php

namespace App\Rules\Contacts;

use App\Models\Contacts\Contact;
use Illuminate\Contracts\Validation\Rule;

class UniqPhoneRule implements Rule
{
    private bool $willBeSaved;
    private string $contactName;
    private string $phone;
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
        $_value = preg_replace("/\D+/", "", $value);
        $contact = Contact::findByPhone($_value)->where('id', '<>', $this->contactId)->first();
        if (!$contact) {
            return true;
        }
        $this->phone = $value;
        $this->contactName = $contact->full_name;
        return false;
    }

    public function message(): string
    {
        return sprintf("This phone [%s] already exists. It belongs to [%s].", $this->phone, $this->contactName);
    }
}
