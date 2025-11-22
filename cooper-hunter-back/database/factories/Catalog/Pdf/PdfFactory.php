<?php

namespace Database\Factories\Catalog\Pdf;

use App\Models\Catalog\Pdf\Pdf;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Pdf|Pdf[]|Collection create(array $attrs = [])
 */
class PdfFactory extends BaseFactory
{
    protected $model = Pdf::class;

    public function definition(): array
    {
        return [
            'path' => $this->faker->filePath()
        ];
    }
}
