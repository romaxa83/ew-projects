<?php


namespace App\Traits\Factory;


use App\Models\Phones\Phone;
use Database\Factories\BaseFactory;

/**
 * Trait HasPhonesFactory
 * @package App\Traits\Factory
 *
 * @mixin BaseFactory
 */
trait HasPhonesFactory
{

    public function configure(): static
    {
        return $this->configurePhone();
    }

    protected function configurePhone(): static
    {
        return $this
            ->has(
                Phone::factory(['is_default' => true]),
                'phones'
            )
            ->has(
                Phone::factory()
                    ->count(2)
            );
    }
}
