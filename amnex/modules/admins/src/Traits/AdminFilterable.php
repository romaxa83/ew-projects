<?php

namespace Wezom\Admins\Traits;

use Illuminate\Database\Eloquent\Model;
use Wezom\Admins\GraphQL\Queries\Back\BackAdminRoles;

trait AdminFilterable
{
    /**
     * @param array $fields
     * @param class-string<Model> $modelClass
     * @param callable|null $callback
     * @return void
     */
    private function testFilterableFields(array $fields, string $modelClass, ?callable $callback = null): void
    {
        $perPage = 5;

        foreach ($fields as $field) {
            if (is_int($field['test'])) {
                $query = 'query { %s ( first: %s %s:%s ) { data { id } } }';
            } else {
                $query = 'query { %s ( first: %s %s:"%s" ) { data { id } } }';
            }

            $query = sprintf(
                $query,
                BackAdminRoles::getName(),
                $perPage,
                $field['field'],
                $field['test'],
            );
            $result = $this->postGraphQL(['query' => $query]);
            self::assertEmpty($result->json('errors'));
            $data = $result->json('data.' . BackAdminRoles::getName() . '.data');
            $dataFlatten = collect($data)->flatten()->toArray();

            $filtered = $modelClass::query()
                ->when($callback, $callback)
                ->where($field['callback'])
                ->limit($perPage)
                ->pluck('id')
                ->toArray();
            self::assertEquals(implode(' ', $filtered), implode(' ', $dataFlatten));
        }
    }
}
