<?php


namespace App\GraphQL\Types;


class DownloadType extends BaseType
{
    public const NAME = 'DownloadType';

    public function fields(): array
    {
        return [
            'link' => [
                'type' => NonNullType::string(),
                'description' => 'Link for download file',
            ]
        ];
    }
}
