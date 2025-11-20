<?php

namespace App\Services\ARI\Commands;

use App\Services\ARI\ClientARI;
use App\Services\ARI\Exceptions\ClientAriException;
use App\Services\ARI\Exceptions\CommandException;
use Throwable;

abstract class BaseCommand
{
    public function __construct(protected ClientARI $client)
    {}

    protected function getUri(): ?string
    {
        return null;
    }

    protected function postUri(): ?string
    {
        return null;
    }
    protected function deleteUri(): ?string
    {
        return null;
    }

    public function exec(array $data = [])
    {
        $data = $this->beforeRequest($data);
        try {

            $res = null;

            if($this->getUri()){
                $res = $this->client->get($this->getUri());
            }
            if($this->postUri()){
                $res = $this->client->post($this->getUri());
            }
            if($this->deleteUri()){
                $res = $this->client->delete($this->deleteUri());
            }

            $res = $this->afterRequest($res);

            return $res;
        }
        catch (ClientAriException $e){
            throw new ClientAriException($e->getMessage(), $e->getCode());
        }
        catch (Throwable $e) {
            throw new CommandException($e->getMessage(), $e->getCode());
        }
    }

    protected function beforeRequest(array $data): array
    {
        return $data;
    }

    protected function afterRequest(array $res)
    {
        return $res;
    }
}
