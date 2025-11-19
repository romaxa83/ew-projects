<?php

namespace Wezom\Quotes\Tests\Builders;

use Wezom\Core\Tests\Builders\BaseBuilder;
use Wezom\Quotes\Models\Quote;
use Wezom\Quotes\Models\TerminalDistance;

class QuoteBuilder extends BaseBuilder
{
    public function modelClass(): string
    {
        return Quote::class;
    }

    public function distance(TerminalDistance $model): self
    {
        $this->data['terminal_distance_id'] = $model->id;

        return $this;
    }

    public function is_palletized(bool $value): self
    {
        $this->data['is_palletized'] = $value;

        return $this;
    }

    public function number_pallets(int $value): self
    {
        $this->data['number_pallets'] = $value;

        return $this;
    }

    public function piece_count(int $value): self
    {
        $this->data['piece_count'] = $value;

        return $this;
    }

    public function days_stored(int $value): self
    {
        $this->data['days_stored'] = $value;

        return $this;
    }
}
