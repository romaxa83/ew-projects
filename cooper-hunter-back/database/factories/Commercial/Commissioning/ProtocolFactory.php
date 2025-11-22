<?php

namespace Database\Factories\Commercial\Commissioning;

use App\Enums\Commercial\Commissioning\ProtocolType;
use App\Models\Commercial\Commissioning\Protocol;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|Protocol[]|Protocol create(array $attributes = [])
 */
class ProtocolFactory extends BaseFactory
{
    protected $model = Protocol::class;

    public function definition(): array
    {
        return [
            'type' => ProtocolType::COMMISSIONING,
            'sort' => 1,
        ];
    }
}

