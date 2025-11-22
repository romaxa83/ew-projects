<?php

namespace App\Services\OneC\Commands;

use App\Contracts\Utilities\Dispatchable;
use App\Models\Request\Request;
use App\Services\OneC\Client\RequestClient;
use Exception;
use Throwable;

abstract class BaseCommand
{
    protected null|Request $record = null;
    protected bool $saveData = true;

    public function __construct(
        protected RequestClient $client,
    ) {
        $this->formatDate = config('api.one_c.format.date_time');
    }

    public function handler(Dispatchable $model, array $additions = []): void
    {
        logger_info("BASE COMMAND START");
        $this->beforeRequest($model);
        try {
            $data = $this->transformData($model, $additions);

            $this->record = Request::create(
                Request::DRIVER_ONEC,
                $this->nameCommand(),
                $this->getFullUrl(),
                $this->saveData ? $data : []
            );

            $res = $this->client->postRequest($this->getUri(), $data);

            if (!data_get($res, 'success') && !is_array(data_get($res, 'error'))) {
                throw new Exception(data_get($res, 'error'));
            }

            $this->afterRequest($model, $res);

            $this->record->update([
                'status' => Request::SUCCESS,
                'response_data' => $res,
            ]);
        }
        catch (CommandException $e){
            throw new Exception($e->getMessage());
        }
        catch (Throwable $e) {
            logger_info("ERROR", [$e->getMessage()]);

            $this->ifException($model, $e);

            if ($this->record) {
                $this->record->update([
                    'status' => Request::ERROR,
                    'response_data' => $e->getMessage(),
                ]);
            }
        }
    }

    protected function beforeRequest(Dispatchable $model): void
    {
    }

    abstract public function transformData(
        Dispatchable $model,
        array $additions = []
    ): array;

    abstract protected function nameCommand(): string;

    protected function getFullUrl(): string
    {
        $base = $this->client->baseUrl ?? null;

        return $base . '/' . $this->getUri();
    }

    abstract protected function getUri(): string;

    protected function afterRequest(Dispatchable $model, $response): void
    {}

    protected function ifException(Dispatchable $model, Throwable $e): void
    {}
}
