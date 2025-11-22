<?php

namespace Core\Chat\Repositories;

use Core\Chat\Models\Participation;
use Core\Chat\Traits\HasState;
use Illuminate\Database\Eloquent\Builder;

class ParticipationRepository
{
    use HasState;

    public function __construct(protected Participation $model)
    {
    }

    protected function propertiesToResetState(): array
    {
        return [];
    }

    protected function query(): Participation|Builder
    {
        return $this->model->newQuery();
    }
}
