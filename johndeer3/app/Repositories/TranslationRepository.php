<?php

namespace App\Repositories;

use App\Abstractions\AbstractRepository;
use App\Models\Translate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TranslationRepository extends AbstractRepository
{
    public function query(): Builder
    {
        return Translate::query();
    }

    public function getByAliasAndLang($alias, $lang)
    {
        return $this->query()
            ->where([
                ['model', Translate::TYPE_SITE],
                ['lang', $lang],
                ['alias', $alias]
            ])
            ->first();
    }

    public function getByModel($model)
    {
        return $this->query()
            ->where([
                ['model', $model],
            ])->pluck('text', 'lang');
    }

    public function existByAliasAndLang($alias, $lang): bool
    {
        return $this->query()
            ->where([
                ['model', Translate::TYPE_SITE],
                ['lang', $lang],
                ['alias', $alias]
            ])
            ->exists();
    }

    public function listByAliases(array $aliases, $lang = null): array
    {
        if(null === $lang){
            $lang = \App::getLocale();
        }

        return $this->query()
            ->select(['text', 'alias', 'lang'])
            ->where('lang', $lang)
            ->whereIn('alias', $aliases)
            ->pluck('text', 'alias')
            ->toArray()
            ;
    }

    public function existRoleByEntityId($entityId, $lang = 'en'): bool
    {
        return $this->query()
            ->where([
                ['model', Translate::TYPE_ROLE],
                ['lang', $lang],
                ['entity_id', $entityId]
            ])
            ->exists()
            ;
    }

    public function getTranslationForExcel(): array
    {
        return $this->query()
            ->where('group', Translate::GROUP_EXCEL)
            ->where('lang', \App::getLocale())
            ->get()
            ->pluck('text', 'alias')
            ->toArray();
    }

    public function getObjByIDs(array $ids = []): Collection
    {
        return $this->query()
            ->whereIn('id', $ids)
            ->toBase()
            ->get();
    }

    public function existByGroupAliasLang(
        string $group,
        string $alias,
        string $lang
    ): bool
    {
        return $this->query()
            ->where([
                ['group', $group],
                ['alias', $alias],
                ['lang', $lang],
            ])
            ->exists();
    }

    public function existForCopy($obj, $lang): bool
    {
        $q = $this->query()
            ->where([
                ['model', $obj->model],
                ['alias', $obj->alias],
                ['lang', $lang],
            ]);

        if(isset($obj->entity_type) && $obj->entity_type){
            $q->where('entity_type', $obj->entity_type);
        }
        if(isset($obj->entity_id) && $obj->entity_id){
            $q->where('entity_id', $obj->entity_id);
        }
        if($obj->group){
            $q->where('group', $obj->group);
        }

        return $q->exists();
    }

    public function getAllAsArray($model, $lang = null, $alias = null): ?array
    {
        $query = Translate::query()
            ->select(['text', 'alias', 'lang'])
            ->where('model', $model);

        if($alias){
            $query->where('alias', $alias);
        }
        if($lang){
            if(is_array($lang)){
                $query->whereIn('lang', $lang);
            } elseif (strpos($lang, ',')){
                // убрать данную конструкцию после перехода на версию v2
                $lang = explode(',', $lang);
                $query->whereIn('lang', $lang);
            } else {
                $query->where('lang', $lang);
            }
        }

        return $query->get()->toArray();
    }
}

