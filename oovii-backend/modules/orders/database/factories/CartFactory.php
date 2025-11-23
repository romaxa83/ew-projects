<?php

namespace WezomCms\Orders\Database\Factories;


use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use WezomCms\Orders\Models\Cart;

class CartFactory extends Factory
{
    protected $model = Cart::class;

    public function definition(): array
    {
        return [
            'conditions' => [],
            'hash' => sha1(microtime() . Str::random()),
        ];
    }
}
