<?php

namespace App\Foundations\Modules\History\Repositories;

use App\Foundations\Models\BaseModel;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Foundations\Modules\History\Models\History;
use App\Foundations\Repositories\BaseEloquentRepository;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

final readonly class HistoryRepository extends BaseEloquentRepository
{
    protected function modelClass(): string
    {
        return History::class;
    }

    public function getCustomPagination(
        BaseModel $model,
        array $filters = []
    ): LengthAwarePaginator
    {
        return History::query()
            ->filter($filters)
            ->where(
                [
                    ['model_id', $model->id],
                    ['model_type', defined($model::class . '::MORPH_NAME')
                        ? $model::MORPH_NAME
                        : $model::class],
                ]
            )
            ->where('type', HistoryType::CHANGES)
            ->latest('id')
            ->paginate(
                perPage: $this->getPerPage($filters),
                page: $this->getPage($filters)
            )
            ;
    }

    public function getHistoryUsers(BaseModel $model): Collection
    {
        return User::query()
            ->whereIn(
                'id',
                History::query()
                    ->select('user_id')
                    ->where(
                        [
                            ['model_id', $model->id],
                            ['model_type', defined($model::class . '::MORPH_NAME')
                                ? $model::MORPH_NAME
                                : $model::class],
                        ]
                    )
                    ->whereType(HistoryType::CHANGES)
                    ->getQuery()
            )
            ->orderByRaw('concat(first_name, \' \', last_name) ASC')
            ->get();
    }

}

