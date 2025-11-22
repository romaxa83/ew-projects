<?php

namespace App\Repositories\Commercial;

use App\Models\Commercial\CommercialProject;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class CommercialProjectRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return CommercialProject::query();
    }

    public function forFrontPaginator(
        array $relations = [],
        array $filters = [],
        array $select = ['id'],
    ): LengthAwarePaginator
    {
        return CommercialProject::query()
//            ->select($select)
            ->filter($filters)
            ->with($relations)
            ->with([
                'projectProtocols.protocol',
                'projectProtocols.projectQuestions'
            ])
            ->latest()
            ->paginate(
                perPage: $this->getPerPage($filters),
                page: $this->getPage($filters)
            )
            ;
    }

    public function forFrontPaginatorByUser(
        $user,
        array $relations = [],
        array $filters = [],
    ): LengthAwarePaginator
    {
        return CommercialProject::query()
            ->filter($filters)
            ->where('member_id' , $user->id)
            ->with($relations)
            ->with([
                'projectProtocols.protocol',
                'projectProtocols.projectQuestions'
            ])
            ->latest()
            ->paginate(
                perPage: $this->getPerPage($filters),
                page: $this->getPage($filters)
            )
            ;
    }

    public function projectsPreCommissioning(): Collection
    {
        return $this->modelQuery()
            ->whereNotNull('start_pre_commissioning_date')
            ->whereNull('start_commissioning_date')
            ->get()
            ;
    }

    public function projectsCommissioning(): Collection
    {
        return $this->modelQuery()
            ->whereNotNull('start_commissioning_date')
            ->whereNull('end_commissioning_date')
            ->get()
        ;
    }
}

