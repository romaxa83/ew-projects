<?php

namespace App\Entities\Statistics\Solutions;

use Illuminate\Contracts\Support\Arrayable;

class IndoorEntity implements Arrayable
{
    protected string $unit;
    protected string $type;
    protected string $btu;
    protected string $line_set;

    private function __construct()
    {
    }

    public static function make(mixed $item): self
    {
        $self = new self();

        $self->unit = $item['unit'];
        $self->type = $item['type'];
        $self->btu = filter_var($item['btu'], FILTER_SANITIZE_NUMBER_INT);
        $self->line_set = $item['line_set'];

        return $self;
    }

    public function getTitle(): string
    {
        return $this->unit;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getBtu(): string
    {
        return $this->btu;
    }

    public function getLineSet(): string
    {
        return $this->line_set;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}