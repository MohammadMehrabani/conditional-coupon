<?php

namespace MohammadMehrabani\ConditionalCoupon;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Pipeline\Pipeline;
use MohammadMehrabani\ConditionalCoupon\Enums\CouponStatusEnum;
use MohammadMehrabani\ConditionalCoupon\Exceptions\CouponException;
use MohammadMehrabani\ConditionalCoupon\Exceptions\UnexpectedException;
use MohammadMehrabani\ConditionalCoupon\Models\Coupon;
use MohammadMehrabani\ConditionalCoupon\Models\CouponCondition;

class InquiryCoupon
{
    /**
     * @throws CouponException|BindingResolutionException|UnexpectedException
     */
    public function handle(string $code, int $amount, $locked = false): array
    {
        if ($amount <= 0) {
            throw CouponException::orderIsFree();
        }

        $coupon = Coupon::query()->with(['conditions'])
            ->where('code', $code)
            ->where('status', CouponStatusEnum::ACTIVE)
            ->when($locked, function ($query) {
                return $query->lockForUpdate();
            })
            ->first();

        if (! $coupon) {
            throw CouponException::couponNotExists();
        }

        $conditionsPipeline[] = app()->make(CheckValidCouponCondition::class, [
            'coupon' => $coupon,
            'amount' => $amount,
        ]);

        /** @var CouponCondition $condition */
        foreach ($coupon->conditions as $condition) {

            if (! class_exists($condition->condition)) {
                throw UnexpectedException::build('Class '.$condition->condition.' dose not exists.');
            }

            if (! is_subclass_of($condition->condition, CheckConditionAbstract::class)) {
                throw UnexpectedException::build(
                    'Class '.$condition->condition.
                    ' Not Implemented MohammadMehrabani\ConditionalCoupon\CheckConditionAbstract'
                );
            }

            $conditionsPipeline[] = app()->make($condition->condition, [
                'coupon' => $coupon,
                'condition' => $condition,
                'amount' => $amount,
            ]);
        }

        app(Pipeline::class)->through($conditionsPipeline)->thenReturn();

        [$currency, $discount_amount, $payable_amount] = $this->calculatedPayableAmount($coupon, $amount);

        return [$currency, $amount, $discount_amount, $payable_amount, $coupon];
    }

    /**
     * @throws UnexpectedException
     */
    private function calculatedPayableAmount(Coupon $coupon, int $amount): array
    {
        if (! empty($coupon->discount_amount) && ! empty($coupon->discount_percentage)) {
            $discount_amount = $amount * ($coupon->discount_percentage / 100);
            $discount_amount = $discount_amount > $coupon->discount_amount ? $coupon->discount_amount : $discount_amount;
        } elseif (! empty($coupon->discount_amount) && empty($coupon->discount_percentage)) {
            $discount_amount = $coupon->discount_amount;
        } elseif (! empty($coupon->discount_percentage) && empty($coupon->discount_amount)) {
            $discount_amount = $amount * ($coupon->discount_percentage / 100);
        } else {
            throw UnexpectedException::build('One of discount_amount or discount percentage is required.');
        }

        $payable_amount = $amount - $discount_amount;

        if ($payable_amount < 0) {
            $discount_amount = $amount;
            $payable_amount = 0;
        }

        $currency = $coupon->currency->value;

        return [$currency, $discount_amount, $payable_amount];
    }
}
