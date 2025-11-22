<?php

namespace Tests;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

trait ElasticsearchClear
{
    public function clearElasticsearch(): void
    {
        $es = config('database.es');
        $link = $es['scheme'] . '://' . $es['host'] . ':' . $es['port'];
        $indexes = Http::acceptJson()
            ->withBasicAuth($es['user'], $es['pass'])
            ->get($link . '/_cat/indices')
            ->json();
        if (empty($indexes)) {
            return;
        }
        $prefix = $es['index_prefix'];
        foreach ($indexes as $index) {
            if ($prefix && !Str::start($index, $prefix)) {
                continue;
            }
            Http::acceptJson()
                ->withBasicAuth($es['user'], $es['pass'])
                ->asJson()
                ->post(
                    $link . '/' . $index['index'] . '/_delete_by_query?conflicts=proceed',
                    [
                        'query' => [
                            'match_all' => (object)[]
                        ]
                    ]
                );
        }
    }
}
