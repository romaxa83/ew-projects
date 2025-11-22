<?php

namespace App\DTO\Catalog\Calc;

use App\Traits\AssetData;

final class SparesGroupTranslationDTO
{
    use AssetData;

    private string $name;
    private string $unit;
    private string $lang;

    private function __construct(){}

    public static function byArgs(array $args): self
    {
        self::assetFieldAll($args, 'unit');

        $self = new self();

        $self->name = $args['name'];
        $self->unit = $args['unit'];
        $self->lang = $args['lang'];

        return $self;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function getLang(): string
    {
        return $this->lang;
    }
}
