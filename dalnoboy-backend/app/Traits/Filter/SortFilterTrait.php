<?php


namespace App\Traits\Filter;

use App\Exceptions\Utilities\SortDirectionException;
use App\Exceptions\Utilities\SortFieldException;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;

trait SortFilterTrait
{
    private static array $sortDirections = [
        'asc',
        'desc',
    ];

    /**
     * @param array $sort
     * @throws SortFieldException
     * @throws SortDirectionException
     * @throws Exception
     */
    public function sort(array $sort): void
    {
        foreach ($sort as $item) {
            $item = Str::lower($item);

            [$field, $direction] = explode('-', $item);

            if (!in_array($direction, self::$sortDirections, true)) {
                throw new SortDirectionException();
            }

            if (in_array($field, $this->allowedTranslateOrders(), true)) {
                $this->setOrderByTranslate($field, $direction);
                continue;
            }

            if (array_key_exists($field, $this->allowedOrdersRelations())) {
                $method = $this->allowedOrdersRelations()[$field];
                $this->setOrderByRelation($field, $direction, $method);
                continue;
            }

            if (in_array($field, $this->allowedOrders())) {
                $this->setOrderBy($field, $direction);
                continue;
            }

            throw new SortFieldException();
        }
    }

    protected function allowedOrders(): array
    {
        return [];
    }

    protected function allowedOrdersRelations(): array
    {
        return [];
    }

    protected function allowedTranslateOrders(): array
    {
        return [];
    }

    private function setOrderBy(string $field, string $direction): void
    {
        if ($method = $this->getCustomSortMethod($field)) {
            $this->{$method}($direction);
            return;
        }

        $this->orderQuery($field, $direction);
    }

    private function getCustomSortMethod(string $field): ?string
    {
        $method = 'custom' . Str::studly($field) . 'Sort';

        return method_exists($this, $method) ? $method : null;
    }

    /**
     * @param string $field
     * @param string $direction
     * @param string $method
     * @throws Exception
     */
    protected function setOrderByRelation(string $field, string $direction, string $method): void
    {
        $methods = explode('.', $method);
        $key = array_pop($methods);
        $keyWithTable = "`" . $this->getTableForOrderRelation($methods) . "`.`$key`";

        $field .= "_sort_$direction";

        $model = $this->getModel();
        $table = $model->getTable();
        $tableSub = $table . "_sub";
        $keyForReplace = '__LOCAL_KEY__';

        $query = $model->from("$table as $tableSub")
            ->selectRaw("$keyWithTable as `$field`")
            ->joinRelationship(
                implode('.', $methods)
            )
            ->whereRaw("`$tableSub`.`id` = $keyForReplace")
            ->orderBy($field, $direction)
            ->limit(1)
            ->getQuery();

        $sql = str_replace(
            $keyForReplace,
            "`$table`.`id`",
            preg_replace(
                "/`$table`\./",
                "`$tableSub`.",
                $this->getSqlForBuilder($query)
            )
        );

        $this->orderByRaw("($sql) " . $direction);
    }

    /**
     * @param array $relations
     * @return string
     * @throws Exception
     */
    private function getTableForOrderRelation(array $relations): string
    {
        $models = [];

        foreach ($relations as $relation) {
            $model = empty($models) ? $this->getModel() : end($models)->getModel();

            if (!method_exists($model, $relation)) {
                throw new Exception(__('exceptions.method_not_found', ['method ' => $relation]));
            }

            $models[] = $model->{$relation}();
        }

        return (array_pop($models))->getModel()->getTable();
    }

    private function getSqlForBuilder(Builder $model): string
    {
        $replace = static function ($sql, $bindings) {
            $needle = '?';
            foreach ($bindings as $replace) {
                $pos = strpos($sql, $needle);
                if ($pos !== false) {
                    if (is_string($replace)) {
                        $replace = " '" . addslashes($replace) . "' ";
                    }
                    $sql = substr_replace($sql, $replace, $pos, strlen($needle));
                }
            }
            return $sql;
        };

        return $replace($model->toSql(), $model->getBindings());
    }

    protected function orderQuery(string $field, string $direction): void
    {
        $this->orderBy($field, $direction);
    }

    private function setOrderByTranslate(string $field, string $direction): void
    {
        $model = $this->getModel();
        $modelTable = $model->getTable();
        $translateTable = $model->getTranslationTableName();
        $this->join($translateTable, $translateTable . '.row_id', '=', $modelTable . '.id')
            ->where($translateTable . '.language', defaultLanguage()->slug)
            ->orderBy($translateTable . '.' . $field, $direction);
    }
}
