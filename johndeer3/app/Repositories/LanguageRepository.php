<?php

namespace App\Repositories;

use App\Abstractions\AbstractRepository;
use App\Models\Languages;
use Illuminate\Database\Eloquent\Builder;

class LanguageRepository extends AbstractRepository
{
    public function query(): Builder
    {
        return Languages::query();
    }

    public function getForSelect(): array
    {
        return $this->query()
            ->get()
            ->pluck('name', 'slug')
            ->map(function($item){
                return ucfirst($item);
            })
            ->toArray();
    }

    public function getDefault()
    {
        return $this->query()->where('default', true)->first();
    }

}
