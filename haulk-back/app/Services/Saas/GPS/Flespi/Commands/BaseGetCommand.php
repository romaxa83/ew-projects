<?php

namespace App\Services\Saas\GPS\Flespi\Commands;

use App\Services\Saas\GPS\Flespi\Exceptions\CommandException;
use App\Services\Saas\GPS\Flespi\Exceptions\FlespiException;
use App\Services\Saas\GPS\Flespi\FlespiClient;
use Throwable;

abstract class BaseGetCommand
{
    protected FlespiClient $client;

    public function __construct(FlespiClient $client)
    {
        $this->client = $client;
    }

    abstract protected function getUri(): string;

    public function handler(array $data = [])
    {
        $this->beforeRequest($data);
        try {

            $res = $this->client->get($this->getUri());

            $res = $this->afterRequest($res);

            return $res;
        }
        catch (FlespiException $e){
            throw new FlespiException($e->getMessage(), $e->getCode());
        }
        catch (Throwable $e) {
            throw new CommandException($e->getMessage(), $e->getCode());
        }
    }

    protected function beforeRequest(array $data): void
    {}

    protected function afterRequest(array $res)
    {
        return $res;
    }
}

