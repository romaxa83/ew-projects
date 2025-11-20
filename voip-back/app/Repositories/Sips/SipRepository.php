<?php

namespace App\Repositories\Sips;

use App\Models\Sips\Sip;
use App\Repositories\AbstractRepository;
use Illuminate\Support\Facades\DB;

final class SipRepository extends AbstractRepository
{
    public function modelClass(): string
    {
        return Sip::class;
    }

    public function getSipNumbers(): array
    {
        return DB::table(Sip::TABLE)
            ->select('number')
            ->get()
            ->pluck('number','number')
            ->toArray();
    }
}
