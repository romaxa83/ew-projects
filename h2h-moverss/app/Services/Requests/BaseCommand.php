<?php

namespace App\Services\Requests;

use App\Enums\Common\LogKeyEnum;
use App\Services\Requests\Exceptions\RequestCommandException;
use App\Services\Requests\Exceptions\RequestException;
use Illuminate\Log\Logger;

abstract class BaseCommand implements CommandInterface
{
    private ?Logger $logger = null;

    abstract public function getUri(array $data = null): string;

    abstract public function getMethod(): RequestMethodEnum;

    abstract public function getRequestClient(): RequestClient;

    public function setLogger(Logger $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function exec(mixed $data = [], array $headers = []): mixed
    {
        $data = $this->beforeRequestForData($data);
        $this->writeToLog(LogKeyEnum::Request()." SEND DATA -----------", [
            'uri' => $this->getUri($data),
            'data' => $data
        ]);
        $headers = $this->beforeRequestForHeaders($headers);
        $this->writeToLog(LogKeyEnum::Request()." HEADERS -----------", [
            'headers' => $headers
        ]);

        try {
            $client = $this->getRequestClient();

            $res = match ($this->getMethod()) {
                RequestMethodEnum::Get => $client->get($this->getUri($data), $data, $headers),
                RequestMethodEnum::Post => $client->post($this->getUri($data), $data, $headers),
                RequestMethodEnum::Put => $client->put($this->getUri($data), $data, $headers),
                RequestMethodEnum::Delete => $client->delete($this->getUri($data ?? null), $headers),
                default => throw new RequestException("An unsupported request method is being used"),
            };
        }
        catch (RequestException $e) {
            $this->writeToLog(LogKeyEnum::Request().' FAIL ', [$e->getMessage()]);
            $this->handlerRequestException($e, $data, $headers);
        }
        catch (\Throwable $e) {
            $this->writeToLog(LogKeyEnum::Request().' FAIL ', [$e->getMessage()]);
            throw new RequestCommandException($e->getMessage(), $e->getCode());
        }

        $this->writeToLog(LogKeyEnum::Request()." RESULT -----------", [
            'result' => $res
        ]);

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

    private function writeToLog(string $message, array $context = []): void
    {
        if($this->logger){
            if(str_contains($message, 'FAIL')){
                $this->logger->error($message, $context);
                return;
            }

            $this->logger->info($message, $context);
            return;
        }

        logger_info($message, $context);
    }
}
