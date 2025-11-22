<?php

namespace App\Services\Requests;

use App\Foundations\Enums\LogKeyEnum;
use App\Services\Requests\Exceptions\RequestCommandException;
use App\Services\Requests\Exceptions\RequestException;

abstract class BaseCommand implements CommandInterface
{
    abstract public function getUri(array $data = null): string;

    abstract public function getMethod(): RequestMethodEnum;

    abstract public function getRequestClient(): RequestClient;

    public function exec(mixed $data = [], array $headers = []): mixed
    {
        $data = $this->beforeRequestForData($data);
        logger_info(LogKeyEnum::Request()." SEND DATA -----------", [$data]);
        $headers = $this->beforeRequestForHeaders($headers);
        try {
            $client = $this->getRequestClient();

            $res = match ($this->getMethod()) {
                RequestMethodEnum::Get => $client->get($this->getUri(), $data, $headers),
                RequestMethodEnum::Post => $client->post($this->getUri($data), $data, $headers),
                RequestMethodEnum::Put => $client->put($this->getUri($data), $data, $headers),
                RequestMethodEnum::Put_Async => $client->putAsync($this->getUri($data), $data, $headers),
                RequestMethodEnum::Delete => $client->delete($this->getUri($data ?? null), $headers),
                default => throw new RequestException("An unsupported request method is being used"),
            };
        }
        catch (RequestException $e) {
            logger_info('[request] Failed', [$e->getMessage()]);
            $this->handlerRequestException($e, $data, $headers);
        }
        catch (\Throwable $e) {
            logger_info('[request] Failed', [$e->getMessage()]);
            throw new RequestCommandException($e->getMessage(), $e->getCode());
        }

        return $this->afterRequest($res);
    }

    public function beforeRequestForData(mixed $data): array
    {
        return $data;
    }

    protected function beforeRequestForHeaders(array $headers): array
    {
        return $headers;
    }

    protected function afterRequest(array $res): mixed
    {
        return $res;
    }

    protected function handlerRequestException(\Throwable $e, array $data, array $headers)
    {
        throw new RequestCommandException($e->getMessage(), $e->getCode());
    }
}
