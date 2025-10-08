<?php

namespace MohammadMehrabani\ConditionalCoupon\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use MohammadMehrabani\ConditionalCoupon\Enums\CouponStatusEnum;
use MohammadMehrabani\ConditionalCoupon\Enums\CurrencyEnum;
use MohammadMehrabani\ConditionalCoupon\Models\Coupon;

class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition()
    {
        return [
            'title' => $this->faker->title,
            'code' => Str::random('6'),
            'discount_percentage' => $this->faker->boolean()
                ? $this->faker->randomFloat(1, 10, 20)
                : null,
            'discount_amount' => function (array $attributes) {
                return empty($attributes['discount_percentage'])
                    ? $this->faker->numberBetween(100, 500)
                    : ($this->faker->boolean() ? $this->faker->numberBetween(100, 500) : null);
            },
            'total_count' => $this->faker->numberBetween(10, 500),
            'used_count' => function (array $attributes) {
                return $this->faker->numberBetween(0, $attributes['total_count']);
            },
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->addMonth(),
            'provider' => $this->faker->company,
            'status' => $this->faker->randomElement(array_column(CouponStatusEnum::cases(), 'value')),
            'description' => $this->faker->boolean()
                ? $this->faker->text
                : null,
            'currency' => CurrencyEnum::IRR->value,
        ];
    }
}
