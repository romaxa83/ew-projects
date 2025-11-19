<?php

namespace Wezom\Admins\Traits;

use Illuminate\Database\Eloquent\Model;
use Wezom\Admins\GraphQL\Queries\Back\BackAdminRoles;

trait AdminSortableTrait
{
    /**
     * @param array $fields
     * @param class-string<Model> $modelClass
     * @param callable|null $callback
     * @return void
     */
    private function testSimpleSortableFields(array $fields, string $modelClass, ?callable $callback = null): void
    {
        $perPage = 5;
        $directions = ['asc', 'desc'];

        foreach ($fields as $field) {
            foreach ($directions as $direction) {
                $query = sprintf(
                    'query { %s ( first: %s sort: "%s" ) { data { ' . $field . ' } } }',
                    BackAdminRoles::getName(),
                    $perPage,
                    "$field-$direction"
                );
                $result = $this->postGraphQL(['query' => $query]);
                $data = $result->json('data.' . BackAdminRoles::getName() . '.data');
                $dataFlatten = collect($data)->flatten()->toArray();

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
}
