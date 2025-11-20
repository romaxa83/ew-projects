<?php

namespace App\GraphQL\Types\Files;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Fields\FormattableDateField;
use App\GraphQL\Types\NonNullType;
use GraphQL\Type\Definition\Type;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class FileType extends BaseType
{
    public const NAME = 'File';
    public const MODEL = Media::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'description' => 'Уникальный идентификатор файла',
            ],
            'uuid' => [
                'type' => NonNullType::string(),
                'description' => 'Уникальный идентификатор файла (UUID)',
            ],
            'name' => [
                'type' => NonNullType::string(),
                'description' => 'Название файла (без расширения).'
            ],
            'mime_type' => [
                'type' => Type::string(),
                'description' => 'Тип файла.'
            ],
            'size' => [
                'type' => NonNullType::int(),
                'description' => 'Размер файла в байтах (1МБ = 1024б).'
            ],
            'original_url' => [
                'type' => Type::string(),
                'resolve' => fn(Media $media) => $media->getFullUrl(),
                'description' => 'Ссылка на оригинал файл.'
            ],
            'created_at' => new FormattableDateField,
            'updated_at' => new FormattableDateField,
        ];
    }
}
