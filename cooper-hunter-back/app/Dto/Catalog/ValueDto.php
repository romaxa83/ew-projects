<?php

namespace App\Dto\Catalog;

use App\Models\Catalog\Features\Value;

class ValueDto
{
    private int $featureId;
    private string $title;
    private ?int $metricId;
    private bool $active;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->featureId = $args['feature_id'];
        $self->title = $args['title'];
        $self->active = $args['active'] ?? Value::DEFAULT_ACTIVE;
        $self->metricId = data_get($args, 'metric_id');

        return $self;
    }

    public function getFeatureId(): int
    {
        return $this->featureId;
    }

    public function getMetricId(): ?int
    {
        return $this->metricId;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
