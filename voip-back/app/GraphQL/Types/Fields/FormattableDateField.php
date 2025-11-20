<?php

namespace App\GraphQL\Types\Fields;

use Carbon\Carbon;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Field;

class FormattableDateField extends Field
{
    protected $attributes = [
        'description' => 'Поле, которое выводит дату в нужном формате.',
    ];

    public function __construct(array $settings = [])
    {
        $this->attributes = array_merge($this->attributes, $settings);
    }

    public function type(): Type
    {
        return Type::string();
    }

    public function args(): array
    {
        return [
            'format' => [
                'type' => Type::string(),
                'defaultValue' => 'Y-m-d H:i',
                'description' => 'По умолчанию: Y-m-d H:i',
            ],
        ];
    }

    protected function resolve($root, array $args): ?string
    {
        $date = $root->{$this->getProperty()};

        if (!$date instanceof Carbon) {
            return null;
        }

        return $date->format($args['format']);
    }

    protected function getProperty(): string
    {
        return $this->attributes['alias'] ?? $this->attributes['name'];
    }
}
