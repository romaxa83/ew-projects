<?php

namespace Tests\Traits;

use App\Models\BaseModel;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Database\Eloquent\Model;

trait AdminSortable
{

    private function testSimpleSortableFields(array $fields, string $modelClass, callable|null $callback = null): void
    {
        $perPage = 5;
        $directions = ['asc', 'desc'];

        foreach ($fields as $field) {
            foreach ($directions as $direction) {
                $query = GraphQLQuery::query(self::QUERY)
                    ->args(
                        [
                            'per_page' => $perPage,
                            'sort' => [
                                $field . '-' . $direction
                            ]
                        ]
                    )
                    ->select(
                        [
                            'data' => [
                                $field
                            ]
                        ]
                    )
                    ->make();
                $result = collect(
                    $this->postGraphQLBackOffice($query)
                        ->json('data.' . self::QUERY . '.data')
                )
                    ->flatten()
                    ->implode(' ');

                /** @var BaseModel $modelClass */
                $sorted = $modelClass::query()
                    ->when($callback, $callback)
                    ->orderBy($field, $direction)
                    ->limit($perPage)
                    ->pluck($field)
                    ->implode(' ');

                self::assertEquals($result, $sorted);
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
                $query = GraphQLQuery::query(self::QUERY)
                    ->args(
                        [
                            'per_page' => $perPage,
                            'sort' => [
                                $field . '-' . $direction
                            ]
                        ]
                    )
                    ->select(
                        [
                            'data' => [
                                'translate' => [
                                    $field
                                ]
                            ]
                        ]
                    )
                    ->make();
                $result = collect(
                    $this->postGraphQLBackOffice($query)
                        ->json('data.' . self::QUERY . '.data')
                )
                    ->flatten()
                    ->implode(' ');;

                /** @var Model $model */
                $model = app($modelClass);
                $modelTable = $model->getTable();
                $translateTable = $model->getTranslationTableName();
                $translateField = 'translate' . $field;

                /** @var BaseModel $modelClass */
                $sorted = $modelClass::query()
                    ->when($callback, $callback)
                    ->with('translate')
                    ->limit($perPage)
                    ->join($translateTable, $translateTable . '.row_id', '=', $modelTable . '.id')
                    ->selectSub($translateTable . '.' . $field, $translateField)
                    ->selectSub($translateTable . '.language', 'language')
                    ->where('language', defaultLanguage()->slug)
                    ->orderBy($translateField, $direction)
                    ->get()
                    ->pluck($translateField)
                    ->implode(' ');

                self::assertEquals($result, $sorted);
            }
        }
    }


}
