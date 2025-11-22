<?php

namespace App\Services\Google\Commands\Road;

use App\Services\Google\Commands\RequestCommand;
use App\Services\Google\GoogleApiClient;
use App\Services\Saas\GPS\Flespi\Exceptions\CommandException;
use Google\Cloud\Core\Exception\GoogleException;
use Throwable;

class GetRouteCommand implements RequestCommand
{
    const URI = 'v1/snapToRoads';

    protected GoogleApiClient $client;

    public function __construct(GoogleApiClient $client)
    {
        $this->client = $client;
    }


    public function handler(array $cords = [])
    {
        try {
            $str = '';
            foreach ($cords as $item){
                $str .= implode(',', $item['location']) . '|';
            }
            $str = mb_substr($str, 0, -1);

            $res = $this->client->get(self::URI, $str);

            return $this->normalizeData($res);
        }
        catch (GoogleException $e){
            throw new GoogleException($e->getMessage(), $e->getCode());
        }
        catch (Throwable $e) {
            throw new CommandException($e->getMessage(), $e->getCode());
        }
    }

    private function normalizeData(array $res): array
    {
        $tmp = [];
        foreach ($res['snappedPoints'] ?? [] as $item){
            $tmp[] = [
                'lat' => $item['location']['latitude'],
                'lng' => $item['location']['longitude']
            ];
        }

        return $tmp;
    }
}


