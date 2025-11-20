<?php

namespace App\DTO\Report;

class ImageDto
{
    public $module;
    public $images = [];

    private function __construct()
    {}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->module = $args['module'];
        $self->images = $args['images'];

        return $self;
    }

    public function emptyImages(): bool
    {
        return empty($this->images);
    }
}
