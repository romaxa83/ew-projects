<?php

namespace Tests\Traits;

use App\Models\BaseModel;

trait Filterable
{
    private function testFilterableFields(array $fields, string $modelClass, callable|null $callback = null): void
    {
        $perPage = 5;

        foreach ($fields as $field) {
            if (is_int($field['test'])){
                $query = 'query { %s ( per_page: %s %s:%s ) { data { id } } }';
            } else {
                $query = 'query { %s ( per_page: %s %s:"%s" ) { data { id } } }';
            }

            $query = sprintf(
                $query,
                self::QUERY,
                $perPage,
                $field['field'],
                $field['test'],
            );
            $result = $this->postGraphQL(['query' => $query]);

            self::assertEmpty($result->json('errors'));
            $data = $result->json('data.' . self::QUERY . '.data');
            $dataFlatten = collect($data)->flatten()->toArray();


            /** @var BaseModel $modelClass */
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
