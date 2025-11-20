<?php

namespace Tests\Traits;

trait ResponseStructure
{
    public function structureErrorResponse($msg): array
    {
        return [
            "data" => $msg,
            "success" => false
        ];
    }

    public function structureSuccessResponse($msg): array
    {
        return [
            "data" => $msg,
            "success" => true,
        ];
    }

    public function structureResource(array $data = []): array
    {
        return [
            "data" => $data
        ];
    }

    public function structureTokens(): array
    {
        return [
            "data" => [
                "token_type",
                "expires_in",
                "access_token",
                "refresh_token",
                "isAdmin",
            ],
            "success",
        ];
    }

    public function structureWithPaginate(array $data): array
    {
        return [
            "data" => [
                $data
            ],
            "links" => [
                "first",
                "last",
                "prev",
                "next",
            ],
            "meta" => [
                "current_page",
                "from",
                "last_page",
                "path",
                "per_page",
                "to",
                "total",
            ]
        ];
    }
}

