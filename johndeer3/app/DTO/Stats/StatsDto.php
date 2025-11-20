<?php

namespace App\DTO\Stats;

class StatsDto
{
    public $year;
    public $status;
    public $country;
    public $dealer;
    public $eg;
    public $md;

    public $type;
    public $size;

    public $crop;
    public $feature;

    private function __construct()
    {}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->year = $args['year'] ?? null;
        $self->status = $args['status'] ?? null;
        $self->country = $args['country'] ?? null;
        $self->dealer = $args['dealer'] ?? null;
        $self->eg = $args['eg'] ?? null;
        $self->md = $args['md'] ?? null;
        $self->type = $args['type'] ?? null;
        $self->size = $args['size'] ?? null;
        $self->crop = $args['crop'] ?? null;
        $self->feature = $args['feature'] ?? null;

        return $self;
    }
}
