<?php

namespace Tests\Traits;

use App\Models\BaseModel;
use App\Traits\Model\HasTranslations;
use Illuminate\Database\Eloquent\Model;

trait AdminSortable
{
    private function testSimpleSortableFields(array $fields, string $modelClass, callable|null $callback = null): void
    {
        $perPage = 5;
        $directions = ['asc', 'desc'];

        foreach ($fields as $field) {
            foreach ($directions as $direction) {
                $query = sprintf(
                    'query { %s ( per_page: %s sort: "%s" ) { data { ' . $field . ' } } }',
                    self::QUERY,
                    $perPage,
                    "$field-$direction"
                );
                $result = $this->postGraphQLBackOffice(compact('query'));
                $data = $result->json('data.' . self::QUERY . '.data');
                $dataFlatten = collect($data)->flatten()->toArray();


                /** @var BaseModel $modelClass */
                $sorted = $modelClass::query()
                    ->when($callback, $callback)
                    ->orderBy($field, $direction)
                    ->limit($perPage)
                    ->pluck($field)
                    ->toArray();

                self::assertEquals(implode(' ', $dataFlatten), implode(' ', $sorted));
            }
        }
    }

    private function testTranslateSortableFields(
        array $fields,
        string $modelClass,
        callable|null $callback = null
    ): void {
        $perPage = 5;
        $directions = ['asc', 'desc'];

        foreach ($fields as $field) {
            foreach ($directions as $direction) {
                $query = sprintf(
                    'query { %s ( per_page: %s sort: "%s" ) { data {translation { ' . $field . '} } } }',
                    self::QUERY,
                    $perPage,
                    "$field-$direction"
                );
                $result = $this->postGraphQLBackOffice(compact('query'));
                $data = $result->json('data.' . self::QUERY . '.data');
                $dataFlatten = collect($data)->flatten()->toArray();

                /** @var Model|HasTranslations $model */
                $model = app($modelClass);
                $modelTable = $model->getTable();
                $translateTable = $model::getTranslationTableName();
                $translateField = 'translate' . $field;

                /** @var BaseModel $modelClass */
                $sorted = $modelClass::query()
                    ->when($callback, $callback)
                    ->with('translation')
                    ->limit($perPage)
                    ->join($translateTable, $translateTable . '.row_id', '=', $modelTable . '.id')
                    ->selectSub($translateTable . '.' . $field, $translateField)
                    ->selectSub($translateTable . '.language', 'language')
                    ->where('language', defaultLanguage()->slug)
                    ->orderBy($translateField, $direction)
                    ->get()
                    ->pluck($translateField)
                    ->toArray();

                self::assertEquals(implode(' ', $dataFlatten), implode(' ', $sorted));
            }
        }
    }
}
