<?php

namespace Tests\Traits;

trait ResponseStructure
{
    public function structure(array $data = []): array
    {
        return [
            "data" => $data,
            "links" => $this->linkStructure(),
            "meta" => $this->metaStructure(),
        ];
    }

    public function structureResource(array $data = []): array
    {
        return [
            "data" => $data
        ];
    }

    public function structureErrorResponse($msg): array
    {
        return [
            "data" => $msg,
            "success" => false,
        ];
    }

    public function structureSucessResponse($msg): array
    {
        return [
            "data" => $msg,
            "success" => true,
        ];
    }

    public function schemaResponse(array $msg): array
    {
        return [
            "data" => $msg,
            "success",
        ];
    }

    public function structureResponse(): array
    {
        return [
            "data",
            "success",
        ];
    }

    public function structureTokens(): array
    {
        return [
            "data" => [
                'tokenType',
                'expiresIn',
                'accessToken',
                'refreshToken',
            ],
            "success"
        ];
    }

    public function linkStructure(): array
    {
        return [
            'first',
            'last',
            'prev',
            'next',
        ];
    }

    public function metaStructure(): array
    {
        return [
            'current_page',
            'from',
            'last_page',
            'links',
            'path',
            'per_page',
            'to',
            'total',
        ];
    }
}
