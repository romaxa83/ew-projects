<?php

namespace App\Repositories\Companies;

use App\Models\Companies\Contact;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;

final class ContactRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return Contact::query();
    }
}

