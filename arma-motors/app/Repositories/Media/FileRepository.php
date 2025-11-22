<?php

namespace App\Repositories\Media;

use App\Models\Media\File;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Collection;

class FileRepository extends AbstractRepository
{
    public function query()
    {
        return File::query();
    }

    public function getByModel(string $model, $modelId = null): Collection
    {
        $query = $this->query()->where('entity_type', $model);

        if($modelId){
            $query->where('entity_id', $modelId);
        }

        return $query->get();
    }

    public function getRowsByIds(array $ids): Collection
    {
        return $this->query()
            ->whereIn('id', $ids)
            ->get();
    }

    public function getByModeAndId(
        string $model,
        string $modelClass,
        string $modelId,
        null|string $type = null
    ): Collection
    {
        $query = $this->query()
            ->where('model', $model)
            ->where('entity_type', $modelClass)
            ->where('entity_id', $modelId);

        if($type){
            $query->where('type', $type);
        }

        return $query->get();
    }

    public function getByHash(
        string $hash
    ): ?File
    {
        $query = $this->query()->where('hash', $hash);

        return $query->first();
    }
}
