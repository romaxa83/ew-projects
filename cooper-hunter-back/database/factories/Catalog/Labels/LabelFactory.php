<?php

namespace Database\Factories\Catalog\Labels;

use App\Enums\Catalog\Labels\ColorType;
use App\Models\Catalog\Labels\Label;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|Label[]|Label create(array $attributes = [])
 */
class LabelFactory extends BaseFactory
{
    protected $model = Label::class;

    public function definition(): array
    {
        return [
            'sort' => 1,
            'active' => true,
            'color_type' => ColorType::BLUE(),
        ];
    }
}
