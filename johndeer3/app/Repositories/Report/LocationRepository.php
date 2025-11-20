<?php

namespace App\Repositories\Report;

use App\Abstractions\AbstractRepository;
use App\Models\Report\Location;
use Illuminate\Database\Eloquent\Builder;


class LocationRepository extends AbstractRepository
{
    public function query(): Builder
    {
        return Location::query();
    }

    public function getListByFilter($type, $query, $country = false)
    {
        $q = $this->query()
            ->select($type)
            ->where($type, 'like' , $query. '%')
            ->distinct();

        if($country){
            $country = parseDateForArray($country);
            $q->whereIn('country', $country);
        }
        return $q->get()->pluck($type, $type)->toArray();
    }

    public function countReportByCountryAndYear(string $country, $year, array $statuses = [])
    {
        $country = parseDateForArray($country);
        return $this->query()
            ->with('report')
            ->whereIn('country', $country)
            ->whereHas('report', function($q) use($year, $statuses) {
                return $q
                    ->whereIn('status' , $statuses)
                    ->whereYear('created_at', $year);
            })
            ->count();
    }
}
