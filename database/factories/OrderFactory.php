<?php

namespace MohammadMehrabani\ConditionalCoupon\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MohammadMehrabani\ConditionalCoupon\Tests\Models\Order;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'amount' => $this->faker->numberBetween(100, 500),
        ];
    }
}
