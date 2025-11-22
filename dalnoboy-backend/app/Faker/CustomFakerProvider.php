<?php


namespace App\Faker;


use App\Models\Localization\Language;
use App\Models\Locations\Region;
use Faker\Provider\Base;
use Illuminate\Support\Str;

class CustomFakerProvider extends Base
{
    public function ukrainianPhone(): string
    {
        return '380' . mt_rand(1, 9) . mt_rand(10000000, 99999999);
    }

    public function language(): string
    {
        return Language::inRandomOrder()
            ->first()
            ->slug;
    }

    public function ukrainianRegionId(): int
    {
        return Region::inRandomOrder()
            ->first()->id;
    }

    public function vin(): string
    {
        return Str::upper($this->regexify('[a-hj-npr-z0-9]{11}\d{6}'));
    }

    public function stateNumber(): string
    {
        return Str::upper(Str::random(8));
    }

    public function odo(): int
    {
        return mt_rand(50000, 2000000);
    }
}
