<?php

namespace App\Services\Requests\Ringostat\Commands;

use App\Models\Division;
use App\Services\Requests\Exceptions\RequestCommandException;
use App\Services\Requests\RequestMethodEnum;
use App\Services\Requests\Ringostat\RingostatBaseCommand;

class GetSipStatusCommand extends RingostatBaseCommand
{
    protected ?string $projectId = null;
    protected ?string $authKey = null;

    public function __construct(protected Division $division)
    {
        $this->projectId = $division->miscs['ringostat_project_id'] ?? null;
        $this->authKey = $division->miscs['ringostat_auth_key'] ?? null;
    }

    public function getUri(array $data = null): string
    {
        return config("requests.ringostat.paths.json-rpc");
    }

    public function getMethod(): RequestMethodEnum
    {
        return RequestMethodEnum::Post;
    }

    public function beforeRequestForHeaders(array $headers): array
    {
        if(is_null($this->authKey)){
            throw new RequestCommandException("GetSipStatusCommand need authKey");
        }

        $headers['Auth-key'] = $this->authKey;

        return $headers;
    }

    public function beforeRequestForData(mixed $data): array
    {
        if(is_null($this->projectId)){
            throw new RequestCommandException("GetSipStatusCommand need projectId");
        }

        $data = [
            "id" => 1,
            "jsonrpc" => "2.0",
            "method" => "getProjectStaffListAndDirections",
            "params" => [
                "projectId" => $this->projectId,
                "departmentsFullFormat" => false
            ]
        ];

        return $data;
    }

    public function afterRequest(array $res): mixed
    {
        return $res['result'];
    }
}
