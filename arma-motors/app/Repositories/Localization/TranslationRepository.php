<?php

namespace App\Repositories\Localization;

use App\Models\Localization\Translation;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Collection;


class TranslationRepository extends AbstractRepository
{
    public function query()
    {
        return Translation::query();
    }

    public function getByPlace(string $place): Collection
    {
        return $this->query()
            ->where('place', $place)
            ->get();
    }

    public function getByPlaceForFront(array $place, array $lang = [])
    {
        return $this->query()
            ->select([\DB::raw("GROUP_CONCAT(CONCAT(lang ,':', text), '|') as `translation`"),'key', 'place'])
            ->whereIn('place', $place)
            ->whereIn('lang', $lang)
            ->groupBy(['key', 'place'])
            ->orderByDesc('place')
            ->getQuery()
            ->get()
//            ->toArray()
        ;
    }

    public function getByPlaceAsArray(string $place, array $select = ['key', 'lang', 'text'])
    {
        return $this->query()
            ->select($select)
            ->where('place', $place)
            ->get()
            ->toArray();
    }

    public function getByPlaceAndKey(string $place, string $key, null|string $group = null): Collection
    {
        $query = $this->query()
            ->where('place', $place)
            ->where('key', $key);

        if($group){
            $query->where('group', $group);
        }
        return $query->get();
    }

    public function getByPlaceAndOrKey(string $place, null|string $key = null): Collection
    {
        $query = $this->query()->where('place', $place);

        if($key){
            $query->where('key', $key);
        }

        return $query->get();
    }

    public function getByPlaceAndKeyAndLang(string $place, string $key, string $lang): null|Translation
    {
        return $this->query()
            ->where('place', $place)
            ->where('key', $key)
            ->where('lang', $lang)
            ->first();
    }

    public function getByGroup(string $group): Collection
    {
        return $this->query()
            ->where('group', $group)
            ->whereNotNull('text')
            ->get();
    }

    public function getByLang(string $lang): Collection
    {
        return $this->query()
            ->where('lang', $lang)
            ->get();
    }
}
