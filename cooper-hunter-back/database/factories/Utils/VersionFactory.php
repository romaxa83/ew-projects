<?php

namespace Database\Factories\Utils;

use App\Models\Utils\Version;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|Version[]|Version create(array $attributes = [])
 */
class VersionFactory extends BaseFactory
{
    protected $model = Version::class;

    public function definition(): array
    {
        return [
            'recommended_version' => '1.0.1',
            'required_version' => '1.0.0',
        ];
    }
}
