<?php

namespace MohammadMehrabani\ConditionalCoupon\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MohammadMehrabani\ConditionalCoupon\Models\Coupon;
use MohammadMehrabani\ConditionalCoupon\Models\CouponCondition;

class CouponConditionFactory extends Factory
{
    protected $model = CouponCondition::class;

    public function definition()
    {
        return [
            'coupon_id' => Coupon::factory(),
        ];
    }

    public function forCoupon(Coupon $coupon): Factory
    {
        return $this->state(function (array $attributes) use ($coupon) {
            return [
                'coupon_id' => $coupon->id,
            ];
        });
    }

    public function condition(string $condition): Factory
    {
        return $this->state(function (array $attributes) use ($condition) {
            return [
                'condition' => $condition,
            ];
        });
    }

    public function data(string|int|array $data): Factory
    {
        return $this->state(function (array $attributes) use ($data) {
            return [
                'data' => $data,
            ];
        });
    }
}
