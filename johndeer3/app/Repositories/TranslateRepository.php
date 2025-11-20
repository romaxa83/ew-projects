<?php
//
//namespace App\Repositories;
//
//use App\Models\Translate;
//use Illuminate\Database\Eloquent\Builder;
//use Illuminate\Support\Collection;
//
//class TranslateRepository
//{

//    public function getByAliasAndLang($alias, $lang)
//    {
//        return Translate::query()
//            ->where('model', Translate::TYPE_SITE)
//            ->where('lang', $lang)
//            ->where('alias', $alias)
//            ->first();
//    }

//    public function existByAliasAndLang($alias, $lang)
//    {
//        return Translate::query()
//            ->where('model', Translate::TYPE_SITE)
//            ->where('lang', $lang)
//            ->where('alias', $alias)
//            ->exists();
//    }

//    public function existRoleByEntityId($entityId, $lang = 'en'): bool
//    {
//        return Translate::query()
//            ->where([
//                ['model', Translate::TYPE_ROLE],
//                ['lang', $lang],
//                ['entity_id', $entityId]
//            ])
//            ->exists()
//            ;
//    }

//    public function deleteByModel($model)
//    {
//        Translate::query()->where('model', $model)->delete();
//    }
//
//    public function getByModelAndLangObject($model, $lang)
//    {
//        return Translate::query()
//            ->where('model', $model)
//            ->where('lang', $lang)
//            ->first();
//    }

//    public function getAllAsArray($model, $lang = null, $alias = null): ?array
//    {
//        $query = Translate::query()
//            ->select(['text', 'alias', 'lang'])
//            ->where('model', $model);
//
//        if($alias){
//            $query->where('alias', $alias);
//        }
//        if($lang){
//            $this->moreLangQuery($query, $lang);
//        }
//
//        return $query->get()->toArray();
//    }

//    public function getMoreAsArray($model, $lang)
//    {
//        return Translate::query()
//            ->select(['text', 'alias', 'lang'])
//            ->where('lang', $lang)
//            ->pluck('text', 'alias')
//            ->toArray()
//        ;
//    }

//    public function getByModelAndLang($model, $lang = null)
//    {
//        $query = Translate::query()->where('model', $model);
//
//        if($lang){
//            if(strpos($lang, ',')){
//                $lang = explode(',', $lang);
//
//                $query->whereIn('lang', $lang);
//            } else {
//                $query->where('lang', $lang);
//            }
//        }
//
//        return $query->pluck('text', 'lang');
//    }

//    private function moreLangQuery(Builder $query, $lang)
//    {
//        if(strpos($lang, ',')){
//            $lang = explode(',', $lang);
//
//            return $query->whereIn('lang', $lang);
//        } else {
//            return $query->where('lang', $lang);
//        }
//    }

//    public function getTranslationAliase(array $aliases = [])
//    {
//        return Translate::query()
//            ->whereIn('alias', $aliases)
//            ->where('lang', \App::getLocale())
//            ->get()
//            ->pluck('text', 'alias')
//            ->toArray();
//    }

//    public function getObjByIDs(array $ids = []): Collection
//    {
//        return Translate::query()
//            ->whereIn('id', $ids)
//            ->toBase()
//            ->get();
//    }

//    public function checkExistForFileImport($group, $alias, $lang): bool
//    {
//        return Translate::query()
//            ->where('group', $group)
//            ->where('alias', $alias)
//            ->where('lang', $lang)
//            ->exists();
//    }

//    public function existForCopy($obj, $lang): bool
//    {
//        $q = Translate::query()
//            ->where('model', $obj->model)
//            ->where('alias', $obj->alias)
//            ->where('lang', $lang);
//
//        if($obj->entity_type){
//            $q->where('entity_type', $obj->entity_type);
//        }
//        if($obj->entity_type){
//            $q->where('entity_id', $obj->entity_id);
//        }
//        if($obj->group){
//            $q->where('group', $obj->group);
//        }
//
//        return $q->exists();
//    }
//}
