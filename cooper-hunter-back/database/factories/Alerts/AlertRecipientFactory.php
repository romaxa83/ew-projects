<?php

namespace Database\Factories\Alerts;

use App\Models\Alerts\AlertRecipient;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method Collection|AlertRecipient[]|AlertRecipient create(array $attributes = [])
 */
class AlertRecipientFactory extends Factory
{
    protected $model = AlertRecipient::class;

    public function definition(): array
    {
        return [

        ];
    }
}
