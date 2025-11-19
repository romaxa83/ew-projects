<?php

namespace Wezom\Quotes\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Wezom\Quotes\Enums\QuoteTerminalTypeEnum;
use Wezom\Quotes\Models\QuoteTerminal;

/**
 * @extends Factory<QuoteTerminal>
 */
class TerminalFactory extends Factory
{
    protected $model = QuoteTerminal::class;

    public function definition(): array
    {
        return [
            'quote_id' => QuoteFactory::new(),
            'terminal_id' => TerminalFactory::new(),
            'type' => QuoteTerminalTypeEnum::PICKUP,
        ];
    }
}
