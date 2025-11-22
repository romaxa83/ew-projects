<?php

namespace App\Rules\Contacts;

use App\Models\Contacts\Contact;
use Illuminate\Contracts\Validation\Rule;

class UniqFullNameRule implements Rule
{
    private bool $willBeSaved;
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
        return Contact::whereFullName($value)->where('id', '<>', $this->contactId)->doesntExist();
    }

    public function message(): string
    {
        return "This contact already exists";
    }
}
