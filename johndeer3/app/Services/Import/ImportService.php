<?php

namespace App\Services\Import;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class ImportService
{
    /**
     * @var Client
     */
    private $client;

    const SIZE_PARAMETERS = '/api/demo/sizeParameters';
    const PRODUCT = '/api/demo/products';
    const EG = '/api/demo/equipmentGroups';
    const MD = '/api/demo/modelDescriptions';
    const REGION = '/api/demo/regions';
    const DEALER = '/api/demo/dealers';
    const CLIENT = '/api/demo/clients';
    const TM = '/api/demo/territorialManagers';
    const SM = '/api/demo/salesManagers';
    const MANUFACTURE = '/api/demo/manufactures';

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getData($type)
    {
        return $this->decodeResult(
            $this->client->get($type,[
                'headers' => $this->header(),
            ])
        );
    }

    private function header()
    {
        return [
            'Accept' => 'application/json',
            'Demo-Authorization' => env('JOHN_DEER_API_KEY'),
            'Admin-Panel' => true
        ];
    }

    private function decodeResult(Response $response)
    {
        if($response->getStatusCode() != '200'){
            throw new \Exception('Request for JohnDeer is fail');
        }

        return \GuzzleHttp\json_decode($response->getBody(), true);
    }
}
