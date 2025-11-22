<?php

namespace App\Http\Controllers\Api\Helpers\ES;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

trait ElasticsearchBuilderTrait
{
    private static Client $esClient;

    protected static function esClient(): Client
    {
        if (isset(self::$esClient)) {
            return self::$esClient;
        }
        $client = ClientBuilder::create()
            ->setHosts(
                [
                    config('database.es.scheme') . '://' .
                    config('database.es.host') . ':' .
                    config('database.es.port')
                ]
            );
        if (config('database.es.api_key')) {
            $client->setApiKey(config('database.es.api_id'), config('database.es.api_key'));
        } else {
            $client->setBasicAuthentication(config('database.es.user'), config('database.es.pass'));
        }
        self::$esClient = $client->build();
        return self::$esClient;
    }
}
