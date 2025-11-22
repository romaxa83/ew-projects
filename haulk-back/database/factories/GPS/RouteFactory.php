<?php
namespace Database\Factories\GPS;

use App\Models\GPS\Route;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

class RouteFactory extends Factory
{
    protected $model = Route::class;

    public function definition(): array
    {
        return [
            'date' => CarbonImmutable::now()->startOfDay(),
            'data' => [
                [
                    'location' => [
                        'lat' => -88.999582,
                        'lng' => 42.493112,
                    ],
                    'speeding' => false,
                    'timestamp' => CarbonImmutable::now()->timestamp,
                ],
                [
                    'location' => [
                        'lat' => 88.999582,
                        'lng' => 52.493112,
                    ],
                    'speeding' => false,
                    'timestamp' => CarbonImmutable::now()->timestamp,
                ],
                [
                    'location' => [
                        'lat' => 78.999582,
                        'lng' => 52.493112,
                    ],
                    'speeding' => true,
                    'timestamp' => CarbonImmutable::now()->timestamp,
                ]
            ],
            'truck_id' => null,
            'trailer_id' => null,
            'hash' => null,
        ];
    }
}
